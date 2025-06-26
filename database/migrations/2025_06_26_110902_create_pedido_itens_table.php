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
        Schema::create('pedido_itens', function (Blueprint $table) {
            $table->id();
            $table->string('numero_pedido') -> nullable(); // ex: "93242"
            $table->string('descricao') -> nullable(); // Descrição do item
            $table->integer('quantidade') -> nullable();
            $table->decimal('valor');
            $table->date('data_previsao');
            $table->text('observacoes');
            $table->timestamp('inicio_embalagem');
            $table->timestamp('fim_embalagem');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedido_itens');
    }
};
