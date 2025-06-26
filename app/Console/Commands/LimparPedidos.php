<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LimparPedidos extends Command
{
    protected $signature = 'limpar:pedidos';
    protected $description = 'Remove todos os registros da tabela pedidos';

    public function handle()
    {
        DB::table('pedido_itens')->truncate();
        $this->info('Tabela pedidos limpa com sucesso.');
    }
}
