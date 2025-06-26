<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedido_itens', function (Blueprint $table) {
            $table->string('numero_pedido')->nullable()->change();
            $table->integer('quantidade')->nullable(false)->change();
            $table->decimal('valor')->nullable()->change();
            $table->date('data_previsao')->nullable()->change();
            $table->text('observacoes')->nullable(false)->change();
            $table->timestamp('inicio_embalagem')->nullable()->change();
            $table->timestamp('fim_embalagem')->nullable()->change();
            $table->string('descricao')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('pedido_itens', function (Blueprint $table) {
            // rollback para todos NOT NULL
            $table->string('numero_pedido')->nullable(false)->change();
            $table->integer('quantidade')->nullable()->change();
            $table->decimal('valor')->nullable(false)->change();
            $table->date('data_previsao')->nullable(false)->change();
            $table->text('observacoes')->nullable()->change();
            $table->timestamp('inicio_embalagem')->nullable(false)->change();
            $table->timestamp('fim_embalagem')->nullable(false)->change();
            $table->string('descricao')->nullable()->change();
        });
    }
};
