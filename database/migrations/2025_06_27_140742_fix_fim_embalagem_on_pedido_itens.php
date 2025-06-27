<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixFimEmbalagemOnPedidoItens extends Migration
{
    public function up()
    {
        Schema::table('pedido_itens', function (Blueprint $table) {
            // SQLite não permite change direto, então removemos e adicionamos
            $table->dropColumn('fim_embalagem');
        });

        Schema::table('pedido_itens', function (Blueprint $table) {
            $table->timestamp('fim_embalagem')->nullable()->default(null);
        });
    }

    public function down()
    {
        Schema::table('pedido_itens', function (Blueprint $table) {
            $table->dropColumn('fim_embalagem');
        });

        Schema::table('pedido_itens', function (Blueprint $table) {
            $table->timestamp('fim_embalagem')->nullable(false)->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }
}

