<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    use HasFactory;

    /**
     * 缓存 Key
     */
    public const CACHE_PREFIX = 'player:';

    protected $fillable = [
        'id',
        'name',
        'hp',
        'thew',
        'enemy',
        'attack',
        'defence',
    ];

    public static function getAdventureKey(string $id): string
    {
        return self::CACHE_PREFIX . $id . ':adventure';
    }
}
