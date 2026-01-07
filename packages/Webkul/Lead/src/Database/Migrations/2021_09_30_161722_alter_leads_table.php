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
        $tablePrefix = DB::getTablePrefix();

        Schema::table('leads', function (Blueprint $table) {
            $table->integer('lead_pipeline_stage_id')->after('lead_pipeline_id')->unsigned()->nullable();
            $table->foreign('lead_pipeline_stage_id')->references('id')->on('lead_pipeline_stages')->onDelete('cascade');
        });

        // SQLite doesn't support table prefix in UPDATE, use different approach
        if (DB::connection()->getDriverName() === 'sqlite') {
            $leads = DB::table('leads')->select('id', 'lead_stage_id')->get();
            foreach ($leads as $lead) {
                DB::table('leads')->where('id', $lead->id)->update([
                    'lead_pipeline_stage_id' => $lead->lead_stage_id,
                ]);
            }
        } else {
            DB::table('leads')
                ->update([
                    'leads.lead_pipeline_stage_id' => DB::raw($tablePrefix.'leads.lead_stage_id'),
                ]);
        }

        // SQLite doesn't support dropForeign, so we handle it differently
        if (DB::connection()->getDriverName() === 'sqlite') {
            Schema::table('leads', function (Blueprint $table) {
                $table->dropColumn('lead_stage_id');
            });
        } else {
            Schema::table('leads', function (Blueprint $table) use ($tablePrefix) {
                $table->dropForeign($tablePrefix.'leads_lead_stage_id_foreign');
                $table->dropColumn('lead_stage_id');
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
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(DB::getTablePrefix().'leads_lead_pipeline_stage_id_foreign');
            $table->dropColumn('lead_pipeline_stage_id');

            $table->integer('lead_stage_id')->unsigned();
            $table->foreign('lead_stage_id')->references('id')->on('lead_stages')->onDelete('cascade');
        });
    }
};
