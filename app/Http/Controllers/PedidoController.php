<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\PedidoItem;
use App\Services\OmiePedidoService;
use Illuminate\Support\Facades\Http;


class PedidoController extends Controller
{
public function index(Request $request)
{
    $data = $request->input('data');
    $codigo = $request->input('codigo');

    $pedidos = \App\Models\PedidoItem::query()
        ->when($data, fn($q) => $q->whereDate('inicio_embalagem', $data))
        ->when($codigo, fn($q) => $q->where('numero_pedido', $codigo))
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
        $pedido = PedidoItem::findOrFail($id);
        $pedido->inicio_embalagem = Carbon::now();
        $pedido->save();
        return redirect()->back();
    }

    public function stop($id)
    {
        $pedido = PedidoItem::findOrFail($id);
        $pedido->fim_embalagem = Carbon::now();
        $pedido->save();
        return redirect()->back();
    }
    public function reiniciar($id)
    {
        $pedido = PedidoItem::findOrFail($id);
        $pedido->inicio_embalagem = null;
        $pedido->fim_embalagem = null; // opcional: limpa também o fim
        $pedido->save();

        return redirect()->back()->with('status', 'Início reiniciado.');
    }
    
    public function atualizarPedidos(Request $request)
    {
        set_time_limit(900); // 5 minutos

        $pagina = 1;
        do {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('https://app.omie.com.br/api/v1/produtos/pedido/', [
                'call'       => 'ListarPedidos',
                'app_key'    => config('services.omie.app_key'),
                'app_secret' => config('services.omie.app_secret'),
                'param'      => [[
                    'pagina' => $pagina,
                    'registros_por_pagina' => 50,
                    'apenas_importado_api' => 'N',
                    'status_pedido' => 'FATURADO',
                    'etapa' => 60,
                    "data_faturamento_de" => now()->subDays(45)->format('d/m/Y'),
                    "data_faturamento_ate" => now()->addDays(45)->format('d/m/Y'),
                ]]
            ]);

            $dados = $response->json();

            foreach ($dados['pedido_venda_produto'] ?? [] as $pedido) {
                $numeroPedido = $pedido['cabecalho']['numero_pedido'];

                // CONSULTAR PEDIDO DETALHADO
                $detalhado = Http::post('https://app.omie.com.br/api/v1/produtos/pedido/', [
                    'call'       => 'ConsultarPedido',
                    'app_key'    => config('services.omie.app_key'),
                    'app_secret' => config('services.omie.app_secret'),
                    'param'      => [[ 'numero_pedido' => $numeroPedido ]]
                ])->json();

                $itens = $detalhado['pedido_venda_produto']['det'] ?? [];

                foreach ($itens as $item) {
                    $descricao = $item['produto']['descricao'] ?? 'Sem descrição';
                    $quantidade = $item['produto']['quantidade'] ?? 1;
                    $codigoItem = $item['produto']['codigo'] ?? null;

                    $jaExiste = PedidoItem::where('numero_pedido', $numeroPedido)
                        ->where('codigo_pedido', $codigoItem)
                        ->first();

                    if (!$jaExiste) {
                        PedidoItem::create([
                            'numero_pedido'   => $numeroPedido,
                            'codigo_pedido'     => $codigoItem,
                            'descricao'       => $descricao,
                            'quantidade'      => $quantidade,
                            'observacoes'     => $detalhado['pedido_venda_produto']['observacoes']['obs_venda'] ?? null,
                            'data_previsao' => isset($detalhado['pedido_venda_produto']['cabecalho']['data_previsao'])
                                ? \Carbon\Carbon::createFromFormat('d/m/Y', $detalhado['pedido_venda_produto']['cabecalho']['data_previsao'])
                                : null,                            
                        ]);
                    } elseif (!$jaExiste->inicio_embalagem && !$jaExiste->fim_embalagem) {
                        // Atualiza dados básicos apenas se ainda não iniciado/embalado
                        $jaExiste->update([
                            'descricao'     => $descricao,
                            'quantidade'    => $quantidade,
                            'observacoes'   => $detalhado['pedido_venda_produto']['observacoes']['obs_venda'] ?? null,
                            'data_previsao' => isset($detalhado['pedido_venda_produto']['cabecalho']['data_previsao'])
                                ? \Carbon\Carbon::createFromFormat('d/m/Y', $detalhado['pedido_venda_produto']['cabecalho']['data_previsao'])
                                : null,  
                        ]);
                    }
                }
            }

            $pagina++;
        } while (!empty($dados['pedido_venda_produto']));

        return redirect()->back()->with('success', 'Pedidos e itens atualizados com sucesso.');
    }

    public function atualizarValor(Request $request, PedidoItem $pedido)
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
        $pedidos = PedidoItem::whereNotNull('inicio_embalagem')
                 ->whereNotNull('fim_embalagem')
                 ->get();

        $pedidos = PedidoItem::orderBy('numero_pedido')->get();

        return view('embalagem.embalados', compact('pedidos'));
    }

}
