<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cnx_reports', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('filters'); // date ranges, venues, departments, etc.
            $table->json('columns'); // which custom fields to include
            $table->json('group_by')->nullable(); // grouping options
            $table->enum('chart_type', ['table', 'bar', 'line', 'pie'])->default('table');
            $table->boolean('public')->default(false); // shareable reports
            $table->unsignedInteger('user_id'); // report creator
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cnx_reports');
    }
}
