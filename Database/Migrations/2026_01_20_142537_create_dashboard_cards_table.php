<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDashboardCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cnx_dashboard_cards', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->text('description')->nullable();

            // Calculation configuration (JSON)
            // Example: {"type": "simple", "field_id": 1, "aggregation": "sum"}
            // Example: {"type": "formula", "formula": "sum(price) / sum(attendees)", "fields": {"price": 1, "attendees": 2}}
            $table->json('calculation_config');

            $table->enum('period', ['today', 'week', 'month', 'quarter', 'year', 'all']);
            $table->string('color', 7)->default('#007bff'); // Hex color
            $table->string('icon')->nullable(); // FontAwesome icon class
            $table->integer('position')->default(0); // For ordering
            $table->boolean('active')->default(true);
            $table->unsignedInteger('user_id'); // Who created this card
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cnx_dashboard_cards');
    }
}
