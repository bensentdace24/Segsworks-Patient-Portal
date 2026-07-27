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
        #database structure for patients table
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->foreignID('user_id')->constrained()->cascadeOnDelete();
            $table->string('full_name');
            $table->date('date_of_birth');
            $table->string('gender');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('blood_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
