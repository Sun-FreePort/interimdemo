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
        Schema::create('goods', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('index')->comment('物品索引');
            $table->unsignedInteger('user_id')->comment('所属玩家');
            $table->unsignedMediumInteger('wear')->comment('耐久');
            $table->unsignedMediumInteger('count')->default(1)->comment('数量，具备数量的商品不能获得自定义效果');
            $table->string('effects')->default('{}')->comment('效果');
            $table->unsignedMediumInteger('status')->default(0)->comment('位标记');
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
        Schema::dropIfExists('goods');
    }
};
