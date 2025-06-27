<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn(['quantidade', 'codigo_pedido', 'data_previsao']);
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->integer('quantidade'); // ajuste conforme o tipo original
            $table->string('codigo_pedido'); // ajuste conforme necessário
            $table->date('data_previsao'); // ajuste conforme necessário
        });
    }
};
