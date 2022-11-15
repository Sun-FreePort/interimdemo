<?php

namespace App\Services;

use App\Models\Building;
use App\Models\City;
use App\Models\Email;
use App\Models\Goods;
use App\Models\Order;
use App\Models\Player;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class UserService
{
    /**
     * 获取版本\获取用户信息
     * @return array
     */
    public function getAllInfo(): array
    {
        $data = [
            'player' => Player::query()->find(Auth::id()),
        ];
        $adventure = Redis::get(Player::getAdventureKey(Auth::id())) ?? [];
        $data['goods'] = Goods::query()
            ->where('user_id', Auth::id())
            ->where('status', '&', 1)
            ->get();

        return [
            'ver' => env('APP_VER'),
            'change' => env('APP_CHANGE_VER'),
            'ts' => now()->timestamp,
            'player' => $data['player'],
            'goods' => $data['goods'],
            'adventure' => $adventure,
        ];
    }

    public function initPlayer(int $userID = 0)
    {
        if ((int)$userID === 0) {
            $userID = Auth::id();
        }
        $player = Player::query()->create([
            'id' => $userID,
            'name' => 'Player ' . Str::random(8),
            'hp' => 100,
            'thew' => 100,
            'enemy' => 100,
            'attack' => 2,
            'defence' => 0,
            'nimble' => 3,
            'hp_max' => 100,
            'thew_max' => 100,
            'enemy_max' => 100,
            'attack_max' => 2,
            'defence_max' => 0,
            'nimble_max' => 3,
        ]);

//        Email::query()->create([
//            'sender_id' => 0,
//            'sender' => '系统',
//            'receiver_id' => $userID,
//            'story' => '新居民，欢迎来到小镇，如果你对这里很陌生，广场或许可以帮到你，请自由的探索吧。\n如果你有改善的意见，请来我创建的QQ群：565666475。',
//            'package_json' => '{}',
//        ]);
    }
}
