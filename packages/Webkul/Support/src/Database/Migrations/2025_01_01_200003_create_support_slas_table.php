<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_slas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent']);
            $table->integer('first_response_time'); // minutes
            $table->integer('resolution_time'); // minutes
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('priority');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_slas');
    }
};

