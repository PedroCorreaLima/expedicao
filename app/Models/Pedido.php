<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;



class Pedido extends Model
{
    protected $fillable = [
        'descricao',
        'quantidade',
        'observacoes',
        'inicio_embalagem',
        'fim_embalagem',
        'codigo_pedido',
        'valor',
    ];
}
