<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomFieldDepartmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cnx_custom_field_department', function (Blueprint $table) {
            $table->unsignedInteger('custom_field_id');
            $table->unsignedInteger('department_id');
            $table->foreign('custom_field_id')->references('id')->on('cnx_custom_fields')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('cnx_departments')->onDelete('cascade');
            $table->primary(['custom_field_id', 'department_id'], 'cnx_cf_dept_primary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cnx_custom_field_department');
    }
}