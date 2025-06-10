<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OmiePedidoService
{
    public function consultarPedidoPorCodigo($numeroPedido)
    {
        return Http::post(config('services.omie.endpoint'), [
            "call" => "ConsultarPedido",
            "app_key" => config('services.omie.app_key'),
            "app_secret" => config('services.omie.app_secret'),
            "param" => [
                [
                    "numero_pedido" => (int) $numeroPedido
                ]
            ]
        ])->json();
    }   
}
