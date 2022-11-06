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
        Schema::create('dict_goods', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('name')->comment('名字');
            $table->string('desc', 40)->comment('简述');
            $table->unsignedMediumInteger('wear')->comment('标准耐久');
            $table->string('effects')->default('{}')->comment('效果');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dict_goods');
    }
};
