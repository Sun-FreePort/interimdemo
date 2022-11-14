<?php

namespace App\Http\Controllers;

use App\Models\Goods;
use App\Models\Player;
use Illuminate\Cache\RedisLock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class MarketController extends Controller
{
    public function orderGet(Request $request)
    {
        $params = $request->validate([
            'city_id' => ['required', 'int', 'min:1'],
            'index' => ['required', 'int', 'min:1'],
            'page' => ['required', 'int', 'min:1'],
            'count' => ['required', 'int', 'in:10,15,20'],
        ]);
        $offset = $params['page'] * $params['count'] - $params['page'];

        return Order::query()
            ->where('city_id', $params['city_id'])
            ->where('index', $params['index'])
            ->offset($offset)
            ->limit(10)
            ->orderByDesc('price')
            ->get();
    }

    public function orderBuy(Request $request)
    {
        $params = $request->validate([
            'id' => ['required', 'int', 'min:1'],
            'count' => ['required', 'int', 'min:1'],
            'force' => ['required', 'int', 'in:0,1'],
        ]);

        // 价格纳入玩家账户；税赋 + 价格从买家手中扣除
        $lock = (new RedisLock(Redis::connection(), Order::LOCK_KEY . $params['id'], 3));

        $order = Order::query()->find($params['id']);
        if (!$order || !$lock) {
            $this->throwError(403, 'orderNotFound', $lock);
        }

        if ($order->count < $params['count']) {
            if ((int)$params['force'] === 0) {
                $lock->release();
                abort(403, 'goodsLacking');
            }
            $params['count'] = $order->count;
        }
        $order->count -= $params['count'];

        $player = Player::query()->find(Auth::id());
        if ($player->local != $order->city_id) {
            $this->throwError(403, 'localAmiss', $lock);
        }

        $moneyNeed = $order->price * $params['count'];
        if ($player->money < $moneyNeed) {
            $this->throwError(403, 'moneyLacking', $lock);
        }
        $player->money -= $moneyNeed;

        DB::transaction(function () use ($order, $player, $params, $moneyNeed) {
            Goods::addGoods([
                'user_id' => Auth::id(),
                'count' => $params['count'],
                'index' => $order->index,
                'city_id' => $order->city_id,
            ]);
            // 发送收益邮件给卖家，从而规避锁冲突
            Email::add([
                'sender_id' => 0,
                'sender' => '系统',
                'receiver_id' => $order->user_id,
                'story' => '订单收益：' . $moneyNeed . "，出售商品 {$params['count']} 个（ID：{$order->index}）",
                'package_json' => json_encode([
                    'money' => $moneyNeed,
                ]),
            ]);
            $player->save();
            if (!$order->count) {
                $order->delete();
            } else {
                $order->save();
            }
        });

        $lock->release();
        return $params['count'];
    }

    public function orderSet(Request $request)
    {
        $params = $request->validate([
            'index' => ['required', 'int', 'min:1'],
            'count' => ['required', 'int', 'min:1'],
            'price' => ['required', 'int', 'min:1'],
        ]);
        $player = Player::query()->find(Auth::id());
        $goods = Goods::query()
            ->where('user_id', Auth::id())
            ->where('index', $params['index'])
            ->where('city_id', $player->local)
            ->where('active', 1)
            ->first();
        if (!$goods || $goods->count < $params['count']) {
            abort(403, 'goodsLacking');
        }

        $rateMoney = floor($params['price'] * $params['count'] * Order::GOODS_RATE);
        if ($player->money < $rateMoney) {
            abort(403, 'moneyLacking');
        }
        $player->money -= $rateMoney;
        $orderData = [
            'city_id' => $player->local,
            'user_id' => Auth::id(),
            'price' => $params['price'],
            'count' => $params['count'],
            'rate_money' => $rateMoney,
            'index' => $params['index'],
            'wear' => $goods->wear,
            'effect_json' => $goods->effect_json,
        ];

        DB::transaction(function () use ($orderData, $player, $params) {
            Order::query()->create($orderData);
            $player->save();
        });
    }

    public function orderDel(Request $request): void
    {
        $params = $request->validate([
            'id' => ['required', 'int', 'min:1'],
        ]);

        $lock = (new RedisLock(Redis::connection(), Order::LOCK_KEY . $params['id'], 3));
        $order = Order::query()->find($params['id']);
        if ($order) {
            DB::transaction(function () use ($order) {
                Player::query()->where('user_id', Auth::id())->update([
                    'rate_money' => ceil($order->rate_money * 0.8),
                ]);
                Goods::addGoods([
                    'user_id' => Auth::id(),
                    'count' => $order->count,
                    'index' => $order->index,
                    'city_id' => $order->city_id,
                ]);
                $order->delete();
            });
        }
        $lock->release();
    }

    private function throwError(int $code, string $message, RedisLock $lock = null)
    {
        $lock?->release();
        abort($code, $message);
    }
}
