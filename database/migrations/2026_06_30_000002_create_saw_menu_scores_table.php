<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saw_menu_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->string('criteria_key', 50);
            $table->decimal('score', 10, 4)->default(0);
            $table->timestamps();

            $table->unique(['menu_id', 'criteria_key']);
            $table->index('criteria_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saw_menu_scores');
    }
};
