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
            $table->string('name', 12)->comment('地名');
            $table->string('monsters')->comment('怪物 ID 列表，按,分割');
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
