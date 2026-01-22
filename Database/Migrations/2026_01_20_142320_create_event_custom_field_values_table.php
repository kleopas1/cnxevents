<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventCustomFieldValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cnx_event_custom_field_values', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('event_id');
            $table->unsignedInteger('custom_field_id');
            $table->text('value')->nullable();
            $table->timestamps();

            $table->foreign('event_id')->references('id')->on('cnx_events')->onDelete('cascade');
            $table->foreign('custom_field_id')->references('id')->on('cnx_custom_fields')->onDelete('cascade');

            $table->unique(['event_id', 'custom_field_id'], 'cnx_event_cf_unique');
            $table->index(['custom_field_id']);
            $table->index(['event_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cnx_event_custom_field_values');
    }
}
