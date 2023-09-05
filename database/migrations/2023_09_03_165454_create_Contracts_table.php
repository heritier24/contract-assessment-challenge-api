<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('Contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("employee_id");
            $table->string("contractType");
            $table->string("startDate");
            $table->string("endDate");
            $table->string("description");
            $table->string("status")->default("active");
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('Employees')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('company_id')->references('id')->on('Company')
                ->onDelete('restrict')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Contracts');
    }
};
