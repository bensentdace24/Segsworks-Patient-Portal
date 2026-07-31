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
        Schema::table('cases', function (Blueprint $table) {
            $table->foreignId('appointment_id')
                ->nullable()
                ->after('patient_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->text('diagnosis')->nullable();
            $table->text('consultation_notes')->nullable();
            $table->text('prescription')->nullable();
            $table->text('treatment_plan')->nullable();
            $table->text('follow_up_instructions')->nullable();

            $table->foreignId('completed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('appointment_id');
            $table->dropConstrainedForeignId('completed_by');

            $table->dropColumn([
                'diagnosis',
                'consultation_notes',
                'prescription',
                'treatment_plan',
                'follow_up_instructions',
            ]);
        });
    }
};
