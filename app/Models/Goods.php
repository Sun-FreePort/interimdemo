<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Goods extends Model
{
    use HasFactory;

    /**
     * 位状态：已被装备
     */
    public const STATUS_EQUIP = 1;

    protected $fillable = [
        'index',
        'user_id',
        'wear',
        'order',
        'count',
        'effects',
        'status',
    ];

    /**
     * @param array $item
     * @param array $dict
     */
    public function add(array $item, array $dict = [])
    {
        if (count($dict) === 0) {
            $dict = DictGoods::get($item['index']);
        }

        $goods = null;
        if ($dict['type'] === DictGoods::TYPE_NORMAL) {
            if ($item['id'] ?? false) {
                $goods = Goods::query()
                    ->find($item['id']);
            } else {
                $goods = Goods::query()
                    ->where('user_id', $item['user_id'])
                    ->where('index', $item['index'])
                    ->first();
            }
        }

        if ($goods) {
            $goods->count += $item['count'];
            $goods->save();
        } else {
            unset($item['id']);
            $item['wear'] = $item['wear'] ?? $dict['wear'];
            Goods::query()->create($item);
        }
    }
}
