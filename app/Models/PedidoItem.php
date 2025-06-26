<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoItem extends Model
{
    protected $table = 'pedido_itens';

    protected $fillable = [
        'numero_pedido',      // ex: "93242"
        'descricao',
        'quantidade',
        'valor',
        'data_previsao',
        'observacoes',
        'inicio_embalagem',
        'fim_embalagem',
    ];
}