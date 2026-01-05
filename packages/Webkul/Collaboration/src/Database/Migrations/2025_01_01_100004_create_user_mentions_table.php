<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_mentions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('message_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('channel_id');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->foreign('message_id')->references('id')->on('chat_messages')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('channel_id')->references('id')->on('chat_channels')->onDelete('cascade');
            $table->index('user_id');
            $table->index('channel_id');
            $table->index('read_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_mentions');
    }
};

