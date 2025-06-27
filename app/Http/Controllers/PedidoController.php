<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\PedidoItem;
use App\Models\Pedido;
use App\Services\OmiePedidoService;
use Illuminate\Support\Facades\Http;


class PedidoController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->input('data');
        $codigo = $request->input('codigo');

        $pedidos = Pedido::whereNull('fim_embalagem')
            ->when($codigo, fn($q) => $q->where('numero_pedido', $codigo))
            ->with('itens') // ✅ traz os itens relacionados
            ->orderBy('numero_pedido')
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
        set_time_limit(3000);
        $pagina = 1;

        do {
            try {
                $response = Http::timeout(30)->connectTimeout(10)->retry(3, 1000)
                    ->post('https://app.omie.com.br/api/v1/produtos/pedido/', [
                        'call'       => 'ListarPedidos',
                        'app_key'    => config('services.omie.app_key'),
                        'app_secret' => config('services.omie.app_secret'),
                        'param'      => [[
                            'pagina' => $pagina,
                            'registros_por_pagina' => 50,
                            'apenas_importado_api' => 'N',
                            'status_pedido' => 'FATURADO',
                            'etapa' => 60,
                            'data_faturamento_de' => now()->subDays(45)->format('d/m/Y'),
                            'data_faturamento_ate' => now()->addDays(45)->format('d/m/Y'),
                        ]]
                    ]);

                $body = $response->json();

                if ($response->status() === 500 && str_contains($body['faultstring'] ?? '', 'Não existem registros')) {
                    break;
                }

                if ($response->failed()) {
                    \Log::error("Erro ao listar pedidos na página $pagina: " . json_encode($body));
                    return redirect()->back()->withErrors([
                        'error' => 'Erro na comunicação com a API da Omie.'
                    ]);
                }

                $todosNumeros = collect($body['pedido_venda_produto'] ?? [])->pluck('cabecalho.numero_pedido')->toArray();
                $existentes = Pedido::whereIn('numero_pedido', $todosNumeros)->pluck('numero_pedido')->toArray();

                $novosPedidos = array_filter($body['pedido_venda_produto'] ?? [], function ($pedido) use ($existentes) {
                    return !in_array($pedido['cabecalho']['numero_pedido'], $existentes);
                });

                foreach ($novosPedidos as $pedidoResumo) {
                    $numeroPedido = $pedidoResumo['cabecalho']['numero_pedido'];

                    try {
                        $detalhado = Http::timeout(30)->connectTimeout(10)->retry(3,1000)
                            ->post('https://app.omie.com.br/api/v1/produtos/pedido/', [
                                'call'       => 'ConsultarPedido',
                                'app_key'    => config('services.omie.app_key'),
                                'app_secret' => config('services.omie.app_secret'),
                                'param'      => [[ 'numero_pedido' => $numeroPedido ]]
                            ])->throw()->json();
                    } catch (\Exception $e) {
                        \Log::error("Erro ao consultar detalhes do pedido $numeroPedido: ".$e->getMessage());
                        continue;
                    }

                    $pedidoModel = Pedido::firstOrCreate(
                        ['numero_pedido' => $numeroPedido],
                        [
                            'observacoes'      => $detalhado['pedido_venda_produto']['observacoes']['obs_venda'] ?? null,
                            'inicio_embalagem' => null,
                            'fim_embalagem'    => null
                        ]
                    );

                    foreach ($detalhado['pedido_venda_produto']['det'] ?? [] as $item) {
                        PedidoItem::firstOrCreate(
                            ['numero_pedido' => $numeroPedido, 'descricao' => $item['produto']['descricao']],
                            ['quantidade' => $item['produto']['quantidade'] ?? 1]
                        );
                    }
                }
            } catch (\Illuminate\Http\Client\RequestException $e) {
                \Log::error("Erro ao listar pedidos na página $pagina: " . $e->getMessage());

                if (str_contains($e->getMessage(), 'Não existem registros para a página')) {
                    break;
                }

                return redirect()->back()->withErrors([
                    'error' => "Erro ao listar pedidos: " . $e->getMessage()
                ]);
            }

            $pagina++;
        } while (!empty($body['pedido_venda_produto']));

        return redirect()->back()->with('success', 'Atualização concluída.');
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
        $pedidos = Pedido::whereNotNull('fim_embalagem')->get(); // ✅ apenas finalizados
        return view('embalagem.embalados', compact('pedidos'));
    }

}
