<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('ai_insights', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type'); // lead_scoring, relationship, opportunity, pipeline
            $table->string('entity_type'); // lead, person
            $table->unsignedInteger('entity_id');
            $table->string('title');
            $table->text('description');
            $table->integer('priority')->default(0); // Higher = more important
            $table->json('metadata')->nullable(); // Additional structured data
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
            $table->index('type');
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_insights');
    }
};
