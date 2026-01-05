<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_channels', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->enum('type', ['direct', 'group'])->default('group');
            $table->text('description')->nullable();
            $table->unsignedInteger('created_by');
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_channels');
    }
};

