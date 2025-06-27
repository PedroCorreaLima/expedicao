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
        Schema::table('pedido_itens', function (Blueprint $table) {
            $table->dropColumn('valor');
        });
    }

    public function down()
    {
        Schema::table('pedido_itens', function (Blueprint $table) {
            $table->float('valor')->nullable(); // ou sem nullable se quiser recriar com not null
        });
    }
};
