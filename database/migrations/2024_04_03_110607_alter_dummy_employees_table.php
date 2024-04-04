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
        Schema::table('dummy_employees', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->bigInteger('mobile')->nullable()->change();
            $table->string('staff_id')->nullable()->change();
            $table->string('place')->nullable()->change();
            $table->date('dob')->nullable()->change();
            $table->string('designation')->nullable()->change();
            $table->string('request_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dummy_employees', function (Blueprint $table) {
            //
        });
    }
};
