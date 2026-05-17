<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Nullable so existing orders (pre-QR) are unaffected
            $table->foreignId('table_id')
                ->nullable()
                ->after('id')
                ->constrained('restaurant_tables')
                ->nullOnDelete();

            // Groups all orders placed in the same QR-scan session
            $table->string('table_session_id')->nullable()->after('table_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['table_id']);
            $table->dropColumn(['table_id', 'table_session_id']);
        });
    }
};
