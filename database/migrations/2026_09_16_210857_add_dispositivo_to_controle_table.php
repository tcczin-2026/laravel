<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('controle', function (Blueprint $table) {
            $table->unsignedBigInteger('dispositivo')->nullable();
            // ajuste o tipo conforme o id do Console (ex: foreignId se preferir)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('controle', function (Blueprint $table) {
            //
        });
    }
};
