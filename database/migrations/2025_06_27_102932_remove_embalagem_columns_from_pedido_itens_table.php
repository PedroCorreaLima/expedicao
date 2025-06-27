<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pedido_itens', function (Blueprint $table) {
            $table->dropColumn(['inicio_embalagem', 'fim_embalagem']);
        });
    }

    public function down(): void
    {
        Schema::table('pedido_itens', function (Blueprint $table) {
            $table->timestamp('inicio_embalagem')->nullable();
            $table->timestamp('fim_embalagem')->nullable();
        });
    }
};
