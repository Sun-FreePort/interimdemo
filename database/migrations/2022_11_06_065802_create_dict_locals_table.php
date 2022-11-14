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
        Schema::create('dict_locals', function (Blueprint $table) {
            $table->id();
            $table->string('name', 14)->comment('地名');
            $table->string('monsters')->comment('怪物 ID、出现概率列表');
            $table->unsignedInteger('east')->comment('东向通往，地点 ID');
            $table->unsignedInteger('west')->comment('西向通往，地点 ID');
            $table->unsignedInteger('north')->comment('北向通往，地点 ID');
            $table->unsignedInteger('south')->comment('南向通往，地点 ID');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dict_locals');
    }
};
