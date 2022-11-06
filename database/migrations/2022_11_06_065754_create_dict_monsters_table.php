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
            $table->unsignedInteger('hp')->comment('生命');
            $table->unsignedInteger('attack')->comment('伤害');
            $table->unsignedInteger('defence')->comment('防御');
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
