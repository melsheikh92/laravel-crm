<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('product_inventories', function (Blueprint $table) {
            // SQLite doesn't support dropping foreign keys - skip on SQLite
            if (DB::connection()->getDriverName() !== 'sqlite') {
                $table->dropForeign(['warehouse_location_id']);
            }

            $table->foreign('warehouse_location_id')->references('id')->on('warehouse_locations')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('product_inventories', function (Blueprint $table) {
            // SQLite doesn't support dropping foreign keys - skip on SQLite
            if (DB::connection()->getDriverName() !== 'sqlite') {
                $table->dropForeign(['warehouse_location_id']);
            }

            $table->foreign('warehouse_location_id')->references('id')->on('warehouse_locations')->onDelete('set null');
        });
    }
};