<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeForeignKeysToRestrict extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Update cnx_events table - venue_id foreign key
        Schema::table('cnx_events', function (Blueprint $table) {
            $table->dropForeign(['venue_id']);
            $table->foreign('venue_id')
                ->references('id')
                ->on('cnx_venues')
                ->onDelete('restrict');
        });

        // Update cnx_event_custom_field_values table - custom_field_id foreign key
        Schema::table('cnx_event_custom_field_values', function (Blueprint $table) {
            $table->dropForeign(['custom_field_id']);
            $table->foreign('custom_field_id')
                ->references('id')
                ->on('cnx_custom_fields')
                ->onDelete('restrict');
        });

        // Update cnx_custom_field_department table - department_id foreign key
        Schema::table('cnx_custom_field_department', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->foreign('department_id')
                ->references('id')
                ->on('cnx_departments')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert cnx_events table - venue_id foreign key
        Schema::table('cnx_events', function (Blueprint $table) {
            $table->dropForeign(['venue_id']);
            $table->foreign('venue_id')
                ->references('id')
                ->on('cnx_venues')
                ->onDelete('cascade');
        });

        // Revert cnx_event_custom_field_values table - custom_field_id foreign key
        Schema::table('cnx_event_custom_field_values', function (Blueprint $table) {
            $table->dropForeign(['custom_field_id']);
            $table->foreign('custom_field_id')
                ->references('id')
                ->on('cnx_custom_fields')
                ->onDelete('cascade');
        });

        // Revert cnx_custom_field_department table - department_id foreign key
        Schema::table('cnx_custom_field_department', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->foreign('department_id')
                ->references('id')
                ->on('cnx_departments')
                ->onDelete('cascade');
        });
    }
}
