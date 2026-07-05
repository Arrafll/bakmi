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
        // menu_reviews becomes a header row (one per order+menu); the actual
        // per-criterion answers move into their own table so a customer can
        // answer the same questionnaire (Harga, Rasa, Popularitas, ...) that
        // admins use when scoring a menu.
        Schema::table('menu_reviews', function (Blueprint $table) {
            $table->dropColumn('rating');
        });

        Schema::create('menu_review_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_review_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_criterion_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('score');
            $table->timestamps();

            $table->unique(['menu_review_id', 'assessment_criterion_id'], 'menu_review_criterion_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_review_scores');

        Schema::table('menu_reviews', function (Blueprint $table) {
            $table->unsignedTinyInteger('rating')->default(1);
        });
    }
};
