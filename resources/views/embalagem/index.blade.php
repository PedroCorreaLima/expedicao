<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Controle de Embalagem</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f4f6f8;
        }

        header {
            background-color: #0b2c4d;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .header-left {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            flex: 1;
        }

        .header-left img {
            height: 200px;
        }

        .nav-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            width: 100%;
        }

        .header-right {
            color: white;
            display: flex;
            flex-direction: column;
            gap: 12px;
            align-items: flex-end;
            flex: 2;
            min-width: 300px;
        }

        h1 {
            color:rgb(233, 237, 240);
            margin-bottom: 20px;
        }

        form {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 0 10px #ccc;
        }

        th, td {
            padding: 10px 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #007BFF;
            color: #fff;
        }

        td pre {
            white-space: pre-wrap;
            word-break: break-word;
        }

        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            text-decoration: none;
        }

        .btn-primary {
            background-color: #007BFF;
            color: white;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-success {
            background-color: #28a745;
            color: white;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .badge {
            background: #28a745;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
        }

        .obs-box {
            max-height: 100px;
            overflow: hidden;
            white-space: pre-wrap;
            cursor: pointer;
            position: relative;
            transition: max-height 0.3s ease;
        }

        .obs-box.expanded {
            max-height: none;
        }

    </style>
<header>
    <div class="header-left">
        <img src="{{ asset('images/logo.png') }}" alt="Logo TecnoPonto">
        <span style="color: #fff; font-weight: bold;">Total Pedidos: {{ $totalPendentes }}</span>

        <div class="nav-buttons">
            <a href="{{ route('embalagem.index') }}" class="btn btn-primary">🚩 Pendentes</a>
            <a href="{{ route('embalagem.embalados') }}" class="btn btn-success">✅ Finalizados</a>
        </div>
    </div>

    <div class="header-right">
        <h1>Controle de Embalagem</h1>

        <form method="GET" action="{{ route('embalagem.index') }}">
            <label for="data">Data:</label>
            <input type="date" name="data" id="data" value="{{ $data ?? '' }}">

            <labelfor="codigo">Código do Pedido:</label>
            <input type="text" name="codigo" id="codigo" value="{{ $codigo ?? '' }}" placeholder="Ex: PED123">

            <button type="submit" class="btn btn-primary">Filtrar</button>
            @if ($data || $codigo)
                <a href="{{ route('embalagem.index') }}" class="btn btn-secondary">Limpar</a>
            @endif
        </form>

        <form method="POST" action="{{ route('pedidos.atualizar') }}">
            @csrf
            <button type="submit" class="btn btn-success">🔄 Atualizar Pedidos Faturados</button>
        </form>
    </div>
</header>
    <table>
        <thead>
        <tr>
            <th>Número</th>
            <th>Descrição</th>
            <th>Qtd.</th>
            <th>Valor Est.</th>
            <th>Observações</th>
            <th>Início</th>
            <th>Fim</th>
            <th>Ações</th>
        </tr>
        </thead>
        <tbody>
            @php
                $lastPedido = null;
                $toggleColor = false;
            @endphp

            @foreach ($pedidos as $pedido)
                @php
                    if ($pedido->numero_pedido !== $lastPedido) {
                        $toggleColor = !$toggleColor;
                        $lastPedido = $pedido->numero_pedido;
                    }
                    $rowClass = $toggleColor ? 'bg-lightblue' : 'bg-white';
                @endphp

                <tr class="{{ $rowClass }}">
                    <style>
                        .bg-lightblue { background-color:rgb(230, 230, 255); } /* Azul claro */
                        .bg-white { background-color: #ffffff; }
                    </style>
                    <td>{{ $pedido->numero_pedido }}</td>
                    <td>{{ $pedido->descricao }}</td>
                    <td>{{ $pedido->quantidade }}</td>
                    <td>
                        <form method="POST" action="{{ route('embalagem.valor', $pedido->id) }}">
                            @csrf
                            <input type="text" name="valor" value="{{ number_format($pedido->valor ?? 0, 2, ',', '.') }}" />
                            <button type="submit">💾</button>
                        </form>
                    </td>
                    <td>
                        <div class="obs-preview" onclick="toggleObservacao(this)">
                            {{ Str::limit($pedido->observacoes, 120) }}
                            <span class="ver-mais">[ver mais]</span>
                        </div>
                        <div class="obs-completo" style="display:none;" onclick="toggleObservacao(this)">
                            {{ $pedido->observacoes }}
                            <span class="ver-menos">[recolher]</span>
                        </div>
                    </td>
                    <td>{{ $pedido->inicio_embalagem ? \Carbon\Carbon::parse($pedido->inicio_embalagem)->format('d/m/Y H:i:s') : '-' }}</td>
                    <td>{{ $pedido->fim_embalagem ? \Carbon\Carbon::parse($pedido->fim_embalagem)->format('d/m/Y H:i:s') : '-' }}</td>
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
                                <button type="submit" class="btn btn-secondary">Reiniciar</button>
                            </form>
                        @else
                            <span class="badge">Concluído</span>
                            <form method="POST" action="{{ route('embalagem.reiniciar', $pedido->id) }}" style="display:inline-block; margin-left:5px;">
                                @csrf
                                <button type="submit" class="btn btn-secondary"
                                        onclick="return confirm('Deseja reiniciar o controle de embalagem deste item?')">
                                    Reiniciar
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<script>
function toggleObservacao(elemento) {
    const preview = elemento.parentElement.querySelector('.obs-preview');
    const completo = elemento.parentElement.querySelector('.obs-completo');

    if (elemento.classList.contains('obs-preview')) {
        preview.style.display = 'none';
        completo.style.display = 'block';
    } else {
        preview.style.display = 'block';
        completo.style.display = 'none';
    }
}
</script>
</body>
</html>
