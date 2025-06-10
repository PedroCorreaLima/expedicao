<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PedidoSeeder extends Seeder
{
    public function run()
    {
        DB::table('pedidos')->insert([
            'descricao' => 'Terminal Biométrico Facial',
            'quantidade' => 5,
            'observacoes' => 'Cliente pediu caixa reforçada',
            'inicio_embalagem' => null,
            'fim_embalagem' => null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}

