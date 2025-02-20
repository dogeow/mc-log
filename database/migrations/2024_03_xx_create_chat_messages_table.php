<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('username');  // 冗余存储用户名，方便查询
            $table->text('content');     // 聊天内容
            $table->string('channel')->default('default');  // 聊天频道，默认为default
            $table->timestamp('sent_at'); // 发送时间
            $table->timestamps();

            // 添加索引
            $table->index('username');
            $table->index('sent_at');
            $table->index('channel');
        });
    }

    public function down()
    {
        Schema::dropIfExists('chat_messages');
    }
}; 