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
        // Every criterion is now computed automatically (customer questionnaire
        // averages for general criteria, order frequency for Popularitas), so
        // there's nothing left that writes to this table.
        Schema::dropIfExists('menu_assessment_scores');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('menu_assessment_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_criterion_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('score');
            $table->timestamps();

            $table->unique(['menu_id', 'assessment_criterion_id'], 'menu_criterion_unique');
        });
    }
};
