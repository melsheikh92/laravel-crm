<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Skip for SQLite as it doesn't support dropping foreign keys
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('product_inventories', function (Blueprint $table) {
            $table->dropForeign(['warehouse_location_id']);

            $table->foreign('warehouse_location_id')->references('id')->on('warehouse_locations')->onDelete('cascade');
        });
    }

    public function down()
    {
        // Skip for SQLite as it doesn't support dropping foreign keys
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('product_inventories', function (Blueprint $table) {
            $table->dropForeign(['warehouse_location_id']);

            $table->foreign('warehouse_location_id')->references('id')->on('warehouse_locations')->onDelete('set null');
        });
    }
};