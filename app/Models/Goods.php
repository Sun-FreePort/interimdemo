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
}
