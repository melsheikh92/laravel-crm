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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_developer')->default(false)->after('status');
            $table->enum('developer_status', ['pending', 'approved', 'rejected', 'suspended'])->nullable()->after('is_developer');
            $table->text('developer_bio')->nullable()->after('developer_status');
            $table->string('developer_company')->nullable()->after('developer_bio');
            $table->string('developer_website')->nullable()->after('developer_company');
            $table->string('developer_support_email')->nullable()->after('developer_website');
            $table->json('developer_social_links')->nullable()->after('developer_support_email');
            $table->timestamp('developer_registered_at')->nullable()->after('developer_social_links');
            $table->timestamp('developer_approved_at')->nullable()->after('developer_registered_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'is_developer',
                'developer_status',
                'developer_bio',
                'developer_company',
                'developer_website',
                'developer_support_email',
                'developer_social_links',
                'developer_registered_at',
                'developer_approved_at',
            ]);
        });
    }
};
