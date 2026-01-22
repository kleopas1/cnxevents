<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cnx_events', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('start_datetime');
            $table->timestamp('end_datetime');
            $table->timestamp('setup_datetime')->nullable();
            $table->timestamp('venue_release_datetime')->nullable();
            $table->boolean('all_day')->default(false);
            $table->unsignedInteger('venue_id');
            $table->enum('status', ['request', 'confirmed'])->default('request');
            $table->unsignedInteger('user_id'); // requester
            $table->string('client_name')->nullable();
            $table->string('client_email')->nullable();
            $table->string('client_phone')->nullable();
            $table->string('client_company')->nullable();
            $table->timestamps();

            $table->foreign('venue_id')->references('id')->on('cnx_venues')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->index(['status']);
            $table->index(['venue_id']);
            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cnx_events');
    }
}