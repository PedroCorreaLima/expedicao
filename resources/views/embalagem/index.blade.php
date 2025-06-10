<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Controle de Embalagem</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 40px;
        }

        h1 {
            margin-bottom: 20px;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 0 10px #ccc;
        }

        th, td {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #007BFF;
            color: #fff;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .btn-success {
            background-color: #28a745;
            color: white;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .text-muted {
            color: #888;
        }
    </style>
</head>
<body>

<h1>Controle de Embalagem</h1>
    <form method="GET" action="{{ route('embalagem.index') }}" style="margin-bottom: 20px;">
        <label for="data">Data:</label>
        <input type="date" name="data" id="data" value="{{ $data ?? '' }}">

        <label for="codigo" style="margin-left: 20px;">Código do Pedido:</label>
        <input type="text" name="codigo" id="codigo" value="{{ $codigo ?? '' }}" placeholder="Ex: PED123">

        <button type="submit" class="btn btn-sm btn-primary" style="margin-left: 10px;">Filtrar</button>

        @if ($data || $codigo)
            <a href="{{ route('embalagem.index') }}" class="btn btn-sm btn-secondary" style="margin-left: 5px;">Limpar</a>
        @endif
    </form>
    @if($codigo && $pedidos->isEmpty())
    <div style="margin-top: 30px;">
        <h4>Resultado da Omie para o código {{ $codigo }}</h4>

        @if(isset($omieDados['pedido_venda_produto']))
            <pre>{{ print_r($omieDados['pedido_venda_produto'], true) }}</pre>
        @elseif(isset($omieDados['faultstring']))
            <div style="color: red;">
                Erro Omie: {{ $omieDados['faultstring'] }}
            </div>
        @else
            <div style="color: orange;">
                Nenhum dado retornado da Omie.
            </div>
        @endif
    </div>
@endif

<table>
    <thead>
        <tr>
        <th>Código</th>
        <th>Descrição</th>
        <th>Quantidade</th>
        <th>Observações</th>
        <th>Início</th>
        <th>Fim</th>
        <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($pedidos as $pedido)
            <tr>
                <td>{{ $pedido->codigo_pedido ?? '-' }}</td>
                <td>{{ $pedido->descricao }}</td>
                <td>{{ $pedido->quantidade }}</td>
                <td>{{ $pedido->observacoes }}</td>
                <td>{{ $pedido->inicio_embalagem ? \Carbon\Carbon::parse($pedido->inicio_embalagem)->format('d/m/Y H:i') : '-' }}</td>
                <td>{{ $pedido->fim_embalagem ? \Carbon\Carbon::parse($pedido->fim_embalagem)->format('d/m/Y H:i') : '-' }}</td>
                <td>
                    @if (!$pedido->inicio_embalagem)
                        <form method="POST" action="{{ route('embalagem.start', $pedido->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-success">Iniciar</button>
                        </form>
                    @elseif (!$pedido->fim_embalagem)
                        <form method="POST" action="{{ route('embalagem.stop', $pedido->id) }}" style="display:inline-block">
                            @csrf
                            <button type="submit" class="btn btn-danger">Finalizar</button>
                        </form>

                        <form method="POST" action="{{ route('embalagem.reiniciar', $pedido->id) }}" style="display:inline-block; margin-left:5px;">
                            @csrf
                            <button type="submit" class="btn btn-warning">Reiniciar Início</button>
                        </form>
                    @else
                        <span class="text-muted">Concluído</span>

                        <form method="POST" action="{{ route('embalagem.reiniciar', $pedido->id) }}" style="display:inline-block; margin-left:5px;">
                            @csrf
                            <button type="submit" class="btn btn-warning"
                                onclick="return confirm('Deseja reiniciar o controle de embalagem deste pedido?')">
                                Reiniciar
                            </button>
                        </form>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
