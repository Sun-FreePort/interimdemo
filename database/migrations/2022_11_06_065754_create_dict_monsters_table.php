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
        Schema::create('dict_monsters', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('称呼');
            $table->unsignedInteger('hp_max')->comment('最大生命');
            $table->unsignedInteger('thew_max')->comment('最大体力');
            $table->unsignedInteger('enemy_max')->comment('最大精力');
            $table->unsignedInteger('attack_max')->comment('最大伤害');
            $table->unsignedInteger('defence_max')->comment('最大防御');
            $table->unsignedInteger('nimble_max')->comment('最大敏捷');
            $table->string('drops')->comment('掉落物，JSON');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dict_monsters');
    }
};
