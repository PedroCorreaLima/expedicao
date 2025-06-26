<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $fillable = [
        'numero_pedido',
        'codigo_pedido',          
        'descricao',
        'quantidade',
        'observacoes',
        'data_previsao',      
        'valor',
        'inicio_embalagem',
        'fim_embalagem',
    ];

    protected $casts = [
        'data_previsao'    => 'date',
        'inicio_embalagem' => 'datetime',
        'fim_embalagem'    => 'datetime',
    ];
}