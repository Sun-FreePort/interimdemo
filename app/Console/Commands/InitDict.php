<?php

namespace App\Console\Commands;

use App\Models\DictGoods;
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
        $temp = [[
            'name' => '',
            'desc' => '',
            'type' => '',
            'wear' => '',
            'effects' => '{}',
        ], [
            'name' => '',
            'desc' => '',
            'type' => '',
            'wear' => '',
            'effects' => '',
        ]];
        DictGoods::query()->create($temp);

        return Command::SUCCESS;
    }
}
