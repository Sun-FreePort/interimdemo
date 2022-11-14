<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;

class DictGoods extends Model
{
    use HasFactory;

    public const CREATED_AT = null;
    public const UPDATED_AT = null;

    /**
     * 缓存前缀
     */
    public const CACHE_PREFIX = 'dict:goods:';

    /**
     * 通用品，可叠加
     */
    public const TYPE_NORMAL = 1;
    /**
     * 特殊品，不可叠加
     */
    public const TYPE_SPECIAL = 2;
    /**
     * 左手装备，不可叠加
     */
    public const TYPE_HAND_LEFT = 3;
    /**
     * 右手装备，不可叠加
     */
    public const TYPE_HAND_RIGHT = 4;
    /**
     * 双手装备，不可叠加（置于左手，右手放置 -1 ID）
     */
    public const TYPE_HAND_PAIR = 5;
    /**
     * 头部装备，不可叠加
     */
    public const TYPE_HEAD = 6;
    /**
     * 胸腹装备，不可叠加
     */
    public const TYPE_CHEST = 7;
    /**
     * 下身装备，不可叠加
     */
    public const TYPE_LEG = 8;
    /**
     * 左脚装备，不可叠加
     */
    public const TYPE_FOOT_LEFT = 9;
    /**
     * 右脚装备，不可叠加
     */
    public const TYPE_FOOT_RIGHT = 10;
    /**
     * 配饰装备，不可叠加
     */
    public const TYPE_FOOT_ACCESSORY = 11;

    public static function get(int $index)
    {
        $data = Redis::get(self::CACHE_PREFIX . $index);
        if (!$data) {
            $data = self::query()->findOrFail($index)->toArray();
            $data['effects'] = json_decode($data['effects'], true);
        } else {
            json_decode($data, true);
        }

        return $data;
    }

    public function isEquip(int $index, array $goodsConfig = []): bool
    {
        if (!count($goodsConfig)) {
            $goodsConfig = self::get($index);
        }
        if (in_array($goodsConfig['type'], [self::TYPE_NORMAL, self::TYPE_SPECIAL])) {
            return false;
        }
        return true;
    }
}
