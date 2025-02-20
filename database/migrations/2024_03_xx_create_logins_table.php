<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('logins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->timestamp('login_at')->nullable();
            $table->timestamp('logout_at')->nullable();
            $table->integer('duration')->nullable(); // 本次登录时长（秒）
            $table->timestamps();
            
            // 添加索引但不添加外键约束
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('logins');
    }
}; 