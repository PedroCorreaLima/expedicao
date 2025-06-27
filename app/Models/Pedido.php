<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $fillable = [
        'numero_pedido',
        'codigo_pedido',          
        'observacoes',
        'valor',
        'inicio_embalagem',
        'fim_embalagem',   
    ];

    protected $casts = [
        'inicio_embalagem' => 'datetime',
        'fim_embalagem'    => 'datetime',
    ];
    
    public function itens()
    {
        return $this->hasMany(PedidoItem::class, 'numero_pedido', 'numero_pedido');
    }
}