<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;



class Pedido extends Model
{
    protected $fillable = [
    'codigo_pedido',
    'descricao',
    'quantidade',
    'observacoes',
    'inicio_embalagem',
    'fim_embalagem',
    ];
}
