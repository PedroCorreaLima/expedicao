<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pedido_itens', function (Blueprint $table) {
            $table->dropColumn('observacoes');
        });
    }

    public function down(): void
    {
        Schema::table('pedido_itens', function (Blueprint $table) {
            $table->text('observacoes'); // ou ->nullable() se antes permitia nulo
        });
    }
};

