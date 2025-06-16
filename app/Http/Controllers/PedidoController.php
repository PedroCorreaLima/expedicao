<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Pedido;
use App\Services\OmiePedidoService;
use Illuminate\Support\Facades\Http;


class PedidoController extends Controller
{
public function index(Request $request)
{
    $data = $request->input('data');
    $codigo = $request->input('codigo');

    $pedidos = \App\Models\Pedido::query()
        ->when($data, fn($q) => $q->whereDate('inicio_embalagem', $data))
        ->when($codigo, fn($q) => $q->where('codigo_pedido', $codigo))
        ->where(function ($q) {
            $q->whereNull('inicio_embalagem')
              ->orWhereNull('fim_embalagem');
        })
        ->get();
        $totalPendentes = $pedidos->count();

    return view('embalagem.index', compact('pedidos', 'data', 'codigo', 'totalPendentes'));
}


    public function start($id)
    {
        $pedido = Pedido::findOrFail($id);
        $pedido->inicio_embalagem = Carbon::now();
        $pedido->save();
        return redirect()->back();
    }

    public function stop($id)
    {
        $pedido = Pedido::findOrFail($id);
        $pedido->fim_embalagem = Carbon::now();
        $pedido->save();
        return redirect()->back();
    }
    public function reiniciar($id)
    {
        $pedido = Pedido::findOrFail($id);
        $pedido->inicio_embalagem = null;
        $pedido->fim_embalagem = null; // opcional: limpa também o fim
        $pedido->save();

        return redirect()->back()->with('status', 'Início reiniciado.');
    }
    
    public function atualizarPedidos(Request $request)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post('https://app.omie.com.br/api/v1/produtos/pedido/', [
            'call'     => 'ListarPedidos',
            'app_key'    => config('services.omie.app_key'),
            'app_secret' => config('services.omie.app_secret'),
            'param'    => [[
                'pagina' => 1,
                'registros_por_pagina' => 200,
                'apenas_importado_api' => 'N',
                'status_pedido' => 'FATURADO',
                'etapa' => 60,
                "data_faturamento_de" => now()->subDays(15)->format('d/m/Y'),
                "data_faturamento_ate" => now()->addDays(15)->format('d/m/Y'),
            ]]
        ]);

        $dados = $response->json();

        foreach ($dados['pedido_venda_produto'] ?? [] as $pedido) {
            $codigoPedido = $pedido['cabecalho']['numero_pedido'];

            if (!Pedido::where('codigo_pedido', $codigoPedido)->exists()) {
                Pedido::create([
                    'codigo_pedido' => $codigoPedido,
                    'descricao' => $pedido['det'][0]['produto']['descricao'] ?? 'Sem descrição',
                    'quantidade' => $pedido['cabecalho']['quantidade_itens'] ?? 1,
                    'observacoes' => $pedido['observacoes']['obs_venda'] ?? 'Sem observações',
                    'cliente' => $pedido['cabecalho']['codigo_cliente'],
                    'data_previsao' => $pedido['cabecalho']['data_previsao'] ?? null,
                    'status' => 'FATURADO',
                    'etapa' => 60,
                    'dados_brutos' => json_encode($pedido),
                ]);
            }
        }
        return redirect()->back()->with('success', 'Pedidos atualizados com sucesso.');
    }

    public function atualizarValor(Request $request, Pedido $pedido)
    {
        $valor = str_replace(',', '.', $request->input('valor'));

        \Log::info("Atualizando valor para o pedido #{$pedido->id}: R$ {$valor}");

        $pedido->update([
            'valor' => $valor,
        ]);

        return redirect()->back()->with('success', 'Valor atualizado.');
    }


    public function embalados(Request $request)
    {
        $pedidos = Pedido::whereNotNull('inicio_embalagem')
                 ->whereNotNull('fim_embalagem')
                 ->get();

        return view('embalagem.embalados', compact('pedidos'));
    }

}
