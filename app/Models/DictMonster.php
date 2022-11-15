<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;

class DictMonster extends Model
{
    use HasFactory;

    public const CREATED_AT = null;
    public const UPDATED_AT = null;
    /**
     * 缓存前缀
     */
    public const CACHE_PREFIX = 'dict:monsters:';

    public static function get(int $index): array
    {
        $data = Redis::get(self::CACHE_PREFIX . $index);
        if (!$data) {
            $data = self::query()->findOrFail($index)->toArray();
            $data['drops'] = json_decode($data['drops'], true);
        } else {
            $data = json_decode($data, true);
        }

        return $data;
    }
}
