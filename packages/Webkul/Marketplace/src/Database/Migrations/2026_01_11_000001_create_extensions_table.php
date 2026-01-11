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
        Schema::create('extensions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('long_description')->nullable();
            $table->enum('type', ['plugin', 'theme', 'integration'])->default('plugin');
            $table->decimal('price', 12, 2)->default(0);
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'disabled'])->default('draft');
            $table->unsignedInteger('downloads_count')->default(0);
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->boolean('featured')->default(false);
            $table->string('logo')->nullable();
            $table->json('screenshots')->nullable();
            $table->string('documentation_url')->nullable();
            $table->string('demo_url')->nullable();
            $table->string('repository_url')->nullable();
            $table->string('support_email')->nullable();
            $table->json('tags')->nullable();
            $table->json('requirements')->nullable();

            $table->integer('author_id')->unsigned();
            $table->foreign('author_id')->references('id')->on('users')->onDelete('cascade');

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
        Schema::dropIfExists('extensions');
    }
};
