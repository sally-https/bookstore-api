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
        Schema::rename('verifystudent', 'verify_students');

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('verifystudent', function (Blueprint $table) {
            //
        });
    }
};
