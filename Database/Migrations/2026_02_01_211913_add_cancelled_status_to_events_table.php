<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddCancelledStatusToEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE cnx_events MODIFY COLUMN status ENUM('request', 'confirmed', 'cancelled') DEFAULT 'request'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE cnx_events MODIFY COLUMN status ENUM('request', 'confirmed') DEFAULT 'request'");
    }
}
