<?php

namespace App\Console\Commands;

use App\Models\DictGoods;
use App\Models\DictLocal;
use App\Models\DictMonster;
use Illuminate\Console\Command;

class InitDict extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init:dict';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Init game dict';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->dictGoods();
        $this->dictLocal();
        $this->dictMonster();

        return Command::SUCCESS;
    }

    private function dictGoods()
    {
        DictGoods::query()->delete();
        $temps = [[
            'id' => 1,
            'name' => '止血草',
            'desc' => '一种止血化瘀的药草',
            'type' => DictGoods::TYPE_NORMAL,
            'wear' => 1,
            'effects' => [[
                'addHp' => 10,
            ]],
        ], [
            'id' => 2,
            'name' => '兽肉',
            'desc' => '野兽的肉，有些腥气，生吃要小心寄生虫',
            'type' => DictGoods::TYPE_NORMAL,
            'wear' => 1,
        ]];
        foreach ($temps as &$temp) {
            $temp['effects'] = json_encode($temp['effects'] ?? '{}');
        }
        DictGoods::query()->insert($temps);
    }

    private function dictLocal()
    {
        DictLocal::query()->delete();
        $temps = [[
            'id' => 1,
            'name' => '狐谷',
            'east' => 0,
            'west' => 0,
            'south' => 2,
            'north' => 0,
            'monsters' => [[
                'id' => 1,
                'rate' => 95,
            ], [
                'id' => 2,
                'rate' => 5,
            ]],
        ], [
            'id' => 2,
            'name' => '庆丰镇',
            'east' => 0,
            'west' => 0,
            'south' => 0,
            'north' => 1,
        ]];
        foreach ($temps as &$temp) {
            $temp['monsters'] = json_encode($temp['monsters'] ?? '{}');
        }
        DictLocal::query()->insert($temps);
    }

    private function dictMonster()
    {
        DictMonster::query()->delete();
        $temps = [[
            'id' => 1,
            'name' => '野狐',
            'hp_max' => 9,
            'thew_max' => 0,
            'enemy_max' => 0,
            'attack_max' => 1,
            'defence_max' => 0,
            'nimble_max' => 2,
            'drops' => [[
                'id' => 2,
                'max' => 2,
                'min' => 1,
            ]],
        ], [
            'id' => 2,
            'name' => '猎狐虫草',
            'hp_max' => 14,
            'thew_max' => 0,
            'enemy_max' => 0,
            'attack_max' => 2,
            'defence_max' => 0,
            'nimble_max' => 4,
            'drops' => [[
                'id' => 1,
                'max' => 3,
                'min' => 0,
            ]],
        ]];
        foreach ($temps as &$temp) {
            $temp['drops'] = json_encode($temp['drops'] ?? '{}');
        }
        DictMonster::query()->insert($temps);
    }
}
