<?php

namespace App\Http\Controllers;

use App\Models\DictGoods;
use App\Models\Goods;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class PlayerController extends Controller
{
    public function adventure(): array
    {
        // 查询玩家信息，检查玩家能否冒险
        $userID = Auth::id();
        $adventure = Redis::get(Player::CACHE_PREFIX . "{$userID}:adventure");
        if ($adventure) {
            return $adventure;
        }
        $player = Player::query()->find($userID);
        if ($player->hp > 10) {
            abort(403, 'hpLacking');
        }

        // TODO 获取本次冒险的目的（固定战斗）
        // TODO 获取本地的怪物列表
        // TODO 随机并获取本地的某只怪物的信息
        // TODO 怪物数据缓存到 Redis，返回
    }

    public function fight()
    {
        // TODO 检查玩家在冒险且战斗
        Redis::set(Player::CACHE_PREFIX . 'adventure', $val, 'EX', $this->seconds, 'NX');
        // TODO 获取玩家、怪物信息
        // TODO 按敏捷计算先手
        // TODO 玩家叠加装备增幅并发起攻击，怪物发起攻击
        // TODO 返回回合战斗结果
    }

    public function runaway()
    {
        // TODO 检查玩家在冒险且战斗
        // TODO 获取玩家、怪物信息，计算逃跑概率
        // TODO 返回回合逃跑结果
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
