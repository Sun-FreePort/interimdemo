<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('players', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->string('name')->comment('称呼');

            $table->unsignedInteger('hp')->comment('生命');
            $table->unsignedInteger('thew')->comment('体力');
            $table->unsignedInteger('enemy')->comment('精力');
            $table->unsignedInteger('attack')->comment('伤害');
            $table->unsignedInteger('defence')->comment('防御');

            $table->unsignedInteger('slot_head')->default(0)->comment('头部装备');
            $table->unsignedInteger('slot_chest')->default(0)->comment('胸腹装备');
            $table->unsignedInteger('slot_hand_left')->default(0)->comment('左手装备，双手武器放置于此');
            $table->unsignedInteger('slot_hand_right')->default(0)->comment('右手装备，双手武器时为 -1');
            $table->unsignedInteger('slot_leg')->default(0)->comment('双腿装备');
            $table->unsignedInteger('slot_foot_left')->default(0)->comment('左脚装备');
            $table->unsignedInteger('slot_foot_right')->default(0)->comment('右脚装备');
            $table->unsignedInteger('slot_accessory')->default(0)->comment('配饰装备');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('players');
    }
};
