<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('controle', function (Blueprint $table) {
            $table->foreignId('dispositivo')
                  ->nullable()
                  ->after('quantidade')
                  ->constrained('console')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('controle', function (Blueprint $table) {
            $table->dropForeign(['dispositivo']);
            $table->dropColumn('dispositivo');
        });
    }
};