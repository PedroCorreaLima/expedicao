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
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'numero_pedido', 'numero_pedido');
    }
}