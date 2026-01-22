<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cnx_custom_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->enum('type', ['text', 'select', 'multiselect', 'date', 'integer', 'decimal']);
            $table->json('options')->nullable(); // for select/multiselect options
            $table->boolean('is_required')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cnx_custom_fields');
    }
}