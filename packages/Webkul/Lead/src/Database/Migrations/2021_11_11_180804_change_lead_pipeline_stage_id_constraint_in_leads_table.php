<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        // SQLite doesn't support dropForeign, so we handle it differently
        if (DB::connection()->getDriverName() === 'sqlite') {
            // For SQLite, we just add the foreign key (no need to drop first)
            Schema::table('leads', function (Blueprint $table) {
                $table->foreign('lead_pipeline_stage_id')->references('id')->on('lead_pipeline_stages')->onDelete('set null');
            });
        } else {
            Schema::table('leads', function (Blueprint $table) {
                $table->dropForeign(['lead_pipeline_stage_id']);
                $table->foreign('lead_pipeline_stage_id')->references('id')->on('lead_pipeline_stages')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // SQLite doesn't support dropForeign
        if (DB::connection()->getDriverName() !== 'sqlite') {
            Schema::table('leads', function (Blueprint $table) {
                $table->dropForeign(['lead_pipeline_stage_id']);

                $table->foreign('lead_pipeline_stage_id')->references('id')->on('lead_pipeline_stages')->onDelete('cascade');
            });
        }
    }
};
