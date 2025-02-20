<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('login_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('login_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('world');
            $table->double('x');
            $table->double('y');
            $table->double('z');
            $table->integer('entity_id');
            $table->string('ip', 45);
            $table->timestamps();

            // 添加索引以提高查询性能
            $table->index('world');
            $table->index('ip');
        });
    }

    public function down()
    {
        Schema::dropIfExists('login_locations');
    }
}; 