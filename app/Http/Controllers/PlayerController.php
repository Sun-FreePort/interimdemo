<?php

namespace App\Http\Controllers;

use App\Models\DictGoods;
use App\Models\DictLocal;
use App\Models\DictMonster;
use App\Models\Goods;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class PlayerController extends Controller
{
    /**
     * @throws \Exception
     */
    public function adventure(): array
    {
        // 查询玩家信息，检查玩家能否冒险
        $userID = Auth::id();
        $adventure = Redis::get(Player::getAdventureKey($userID));
        if ($adventure) {
            return $adventure;
        }
        $player = Player::query()->find($userID);
        if ($player->hp > 10) {
            abort(403, 'hpLacking');
        }

        // 获取本次冒险的目的
        $rand = random_int(0, 100);
        switch (true) {
            case $rand < 1000:
                $target = 'fight';
                break;
            default:
                abort(500, 'adventureNotHas');
        }
        // 获取本地的怪物列表
        $local = DictLocal::get($player->local);
        $rates = [];
        $i = -1;
        foreach ($local['monsters'] as $item) {
            if ($i < 0) {
                $rates[] = $item['rate'];
                continue;
            }
            $rates[] = $rates[$i++] + $item['rate'];
        }
        // 随机并获取本地的某只怪物的信息
        $rand = random_int(0, $rates[count($rates) - 1]);
        $monster = null;
        foreach ($rates as $index => $rate) {
            if ($rand <= $rate) {
                $monster = $local['monsters'][$index]['id'];
                break;
            }
        }
        $monster = DictMonster::get($monster);

        // 怪物数据缓存到 Redis，返回
        $monster['hp'] = $monster['hp_max'];
        $monster['thew'] = $monster['thew_max'];
        $monster['enemy'] = $monster['enemy_max'];
        $monster['attack'] = $monster['attack_max'];
        $monster['defence'] = $monster['defence_max'];
        $monster['nimble'] = $monster['nimble_max'];
        $result = [
            'turn' => 0,
            'target' => $target,
            'monster' => $monster,
        ];
        Redis::setex(Player::getAdventureKey($userID), 60 * 30, json_encode($result));

        return $result;
    }

    public function fight(): array
    {
        // 检查玩家在冒险且战斗，获取玩家、怪物信息
        $adventure = json_decode(Redis::get(Player::getAdventureKey(Auth::id())), true);
        if (!$adventure) {
            abort(403, 'adventureNotFound');
        } elseif ($adventure['target'] !== 'fight') {
            abort(403, 'mustInFight');
        }
        $adventure['turn']++;
        $player = Player::query()->find(Auth::id())->toArray();

        // 按敏捷计算先手
        $playerFirst = ($player['nimble'] >= $adventure['monster']['nimble']);

        // 战斗并返回回合结果
        if ($playerFirst) {
            $itemP = $this->fightOnce($player, $adventure['monster']);
        } else {
            $itemM = $this->fightOnce($adventure['monster'], $player);
        }
        $check = $this->fightCheck($player, $adventure['monster']);
        if ($check['failed'] === 0) {
            if ($playerFirst) {
                $itemM = $this->fightOnce($adventure['monster'], $player);
            } else {
                $itemP = $this->fightOnce($player, $adventure['monster']);
            }
        }

        // TODO 装备耐久度降低
        DB::transaction(function () use ($player, $adventure, $check) {
            if (count($check['drops'])) {
                Goods::query()->where('id', $player['id'])->add
            }
            Player::query()->where('id', $player['id'])->update($player);
            Redis::setex(Player::getAdventureKey($player['id']), 60 * 30, json_encode($adventure));
        });

        return [
            'failed' => $check['failed'],
            'drops' => $check['drops'],
            'adventure' => $adventure,
            'player' => $player,
            'playerFirst' => $playerFirst,
            'turnEnemy' => $itemP['turn'] ?? 0, // 回合操作
            'damageEnemy' => $itemP['damage'] ?? 0, // 造成伤害
            'targetEnemy' => $itemP['target'] ?? 0, // 伤害方向
            'turnSelf' => $itemM['turn'] ?? 0,
            'damageSelf' => $itemM['damage'] ?? 0,
            'targetSelf' => $itemM['target'] ?? 0,
        ];
    }

    /**
     * 进行一次战斗
     * @param array $attacker
     * @param array $victim
     * @return int[]
     */
    private function fightOnce(array &$attacker, array &$victim): array
    {
        $turn = -1;
        $damage = -1;
        $target = -1;

        return [
            'turn' => $turn,
            'damage' => $damage,
            'target' => $target,
        ];
    }

    /**
     * 检定战斗是否可继续，如果不可继续，则确认掉落物或结束战斗
     * @param array $player
     * @param array $monster
     * @return array
     */
    private function fightCheck(array $player, array $monster): array
    {
        $result = [
            'failed' => 0,
            'drops' => [],
        ];
        if ($player['hp'] < 1) {
            $result['failed'] = 1;
        }
        if ($monster['hp'] < 1) {
            $result['failed'] = 2;
        }

        return $result;
    }

    /**
     * 逃跑
     * @return array
     */
    public function runaway(): array
    {
        // 检查玩家在冒险且战斗，获取玩家、怪物信息
        $adventure = json_decode(Redis::get(Player::getAdventureKey(Auth::id())), true);
        if (!$adventure) {
            abort(403, 'adventureNotFound');
        } elseif ($adventure['target'] !== 'fight') {
            abort(403, 'mustInFight');
        }
        $adventure['turn']++;
        $player = Player::query()->find(Auth::id());
        Redis::del(Player::getAdventureKey($player->id), 60 * 30, json_encode($adventure));
        // TODO 获取玩家、怪物信息，计算逃跑概率
        $rate = 100;
        // TODO 返回回合逃跑结果

        return [
            'result' => 1,
        ];
    }

    public function equipped(Request $request)
    {
        $params = $request->validate([
            'id' => ['required', 'int', 'min:1'],
        ]);

        // 查看是否已装备
        $player = Player::query()->find(Auth::id());
        if ($player->equip_index) {
            abort(403, 'equipped');
        }
        // 查看工作中——工作中无法切换装备
        if ($player->work_end > 0) {
            abort(403, 'workBusy');
        }
        // 查看物品是否存在
        $goods = Goods::query()
            ->where('id', $params['id'])
            ->where('user_id', $player->id)
            ->where('city_id', $player->local)
            ->where('active', 1)
            ->first();
        if (!$goods) {
            abort(403, 'goodsLacking');
        }
        if ((new DictGoods())->isEquip($goods->index)) {
            abort(403, 'goodsTypeFailed');
        }

        // 将物品换到身上，移除物品表信息
        $player->equip_id = $goods->id;
        $player->equip_index = $goods->index;
        $player->effect = $goods->effect;
        $player->wear = $goods->wear;
        $goods->active = 0;

        DB::transaction(function () use ($player, $goods) {
            $player->save();
            $goods->save();
        });
    }

    public function unequipped(Request $request)
    {
        // 查看是否已装备
        $player = Player::query()->find(Auth::id());
        if (!$player->equip_index) {
            abort(403, 'unequipped');
        }

        // 查看工作中——工作中无法切换装备
        if ($player->work_end > 0) {
            abort(403, 'workBusy');
        }

        // 将物品换到物品表，移除身上的装备信息
        $goods = Goods::query()
            ->where('id', $player->equip_id)
            ->where('user_id', Auth::id())
            ->where('active', 0)
            ->first();
        $player->equip_id = 0;
        $player->equip_index = 0;
        $player->effect = '{}';
        $player->wear = 0;
        $goods->wear = $player->wear;
        $goods->city_id = $player->local;

        DB::transaction(function () use ($player, $goods) {
            $player->save();
            $goods->save();
        });
    }
}
