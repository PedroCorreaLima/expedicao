<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Pedido;
use App\Services\OmiePedidoService;
use Illuminate\Support\Facades\Http;


class PedidoController extends Controller
{
    public function index(Request $request, OmiePedidoService $omie)
    {
        $data = $request->input('data');
        $codigo = $request->input('codigo');
        $omieDados = null;

        $pedidos = \App\Models\Pedido::query()
            ->when($data, fn($q) => $q->whereDate('inicio_embalagem', $data))
            ->when($codigo, fn($q) => $q->where('codigo_pedido', $codigo))
            ->get();

        if ($codigo && $pedidos->isEmpty()) {
            $omieDados = $omie->consultarPedidoPorCodigo($codigo);

            if (isset($omieDados['pedido_venda_produto']['cabecalho'])) {
                $cabecalho = $omieDados['pedido_venda_produto']['cabecalho'];

                // Cria automaticamente o pedido no banco
                \App\Models\Pedido::create([
                    'codigo_pedido' => $cabecalho['numero_pedido'],
                    'descricao' => $omieDados['pedido_venda_produto']['det'][0]['produto']['descricao'] ?? 'Produto não informado',
                    'quantidade' => $cabecalho['quantidade_itens'] ?? 1,
                    'observacoes' => $omieDados['pedido_venda_produto']['observacoes']['obs_venda'] ?? 'Sem observações',
                    'inicio_embalagem' => null,
                    'fim_embalagem' => null,
                ]);

                // Recarrega a lista atualizada
                $pedidos = \App\Models\Pedido::where('codigo_pedido', $codigo)->get();
            }
        }

        return view('embalagem.index', compact('pedidos', 'data', 'codigo', 'omieDados'));
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
                "data_faturamento_de" => "16/06/2024",
                "data_faturamento_ate" => "16/06/2025"
            ]]
        ]);

        $dados = $response->json();

        foreach ($dados['pedido_venda_produto'] ?? [] as $pedido) {
        Pedido::updateOrCreate(
            ['codigo_pedido' => $pedido['cabecalho']['numero_pedido']],
            [
                'descricao' => $pedido['det'][0]['produto']['descricao'] ?? 'Sem descrição',
                'quantidade' => $pedido['cabecalho']['quantidade_itens'] ?? 1,
                'observacoes' => $pedido['observacoes']['obs_venda'] ?? 'Sem observações',
                'cliente' => $pedido['cabecalho']['codigo_cliente'],
                'data_previsao' => $pedido['cabecalho']['data_previsao'] ?? null,
                'status' => 'FATURADO',
                'etapa' => 60,
                'dados_brutos' => json_encode($pedido),
            ]
        );
        }
        return redirect()->back()->with('success', 'Pedidos atualizados com sucesso!');
    }
}
