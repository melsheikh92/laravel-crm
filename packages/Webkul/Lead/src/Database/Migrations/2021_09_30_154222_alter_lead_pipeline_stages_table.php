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

        Schema::table('lead_pipeline_stages', function (Blueprint $table) {
            $table->string('code')->after('id')->nullable();
            $table->string('name')->after('code')->nullable();
        });

        // SQLite doesn't support UPDATE with JOIN, use a different approach
        if (DB::connection()->getDriverName() === 'sqlite') {
            $pipelineStages = DB::table('lead_pipeline_stages')
                ->join('lead_stages', 'lead_pipeline_stages.lead_stage_id', '=', 'lead_stages.id')
                ->select('lead_pipeline_stages.id', 'lead_stages.code', 'lead_stages.name')
                ->get();

            foreach ($pipelineStages as $stage) {
                DB::table('lead_pipeline_stages')
                    ->where('id', $stage->id)
                    ->update([
                        'code' => $stage->code,
                        'name' => $stage->name,
                    ]);
            }
        } else {
            DB::table('lead_pipeline_stages')
                ->join('lead_stages', 'lead_pipeline_stages.lead_stage_id', '=', 'lead_stages.id')
                ->update([
                    'lead_pipeline_stages.code' => DB::raw($tablePrefix.'lead_stages.code'),
                    'lead_pipeline_stages.name' => DB::raw($tablePrefix.'lead_stages.name'),
                ]);
        }

        // SQLite doesn't support dropForeign, so we handle it differently
        if (DB::connection()->getDriverName() === 'sqlite') {
            Schema::table('lead_pipeline_stages', function (Blueprint $table) {
                $table->dropColumn('lead_stage_id');
                $table->unique(['code', 'lead_pipeline_id']);
                $table->unique(['name', 'lead_pipeline_id']);
            });
        } else {
            Schema::table('lead_pipeline_stages', function (Blueprint $table) use ($tablePrefix) {
                $table->dropForeign($tablePrefix.'lead_pipeline_stages_lead_stage_id_foreign');
                $table->dropColumn('lead_stage_id');
                $table->unique(['code', 'lead_pipeline_id']);
                $table->unique(['name', 'lead_pipeline_id']);
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
        Schema::table('lead_pipeline_stages', function (Blueprint $table) {
            $table->dropColumn('code');
            $table->dropColumn('name');

            $table->integer('lead_stage_id')->unsigned();
            $table->foreign('lead_stage_id')->references('id')->on('lead_stages')->onDelete('cascade');

            $table->dropUnique(['lead_pipeline_stages_code_lead_pipeline_id_unique', 'lead_pipeline_stages_name_lead_pipeline_id_unique']);
        });
    }
};
