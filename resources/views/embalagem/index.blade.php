<!DOCTYPE html>
<html lang="pt-BR">
@php use Illuminate\Support\Str; @endphp
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

        .obs-preview, .obs-completo {
        cursor: pointer;
        display: block;
        white-space: pre-line;
        }

        .ver-mais {
            color: blue;
            text-decoration: underline;
            font-size: 0.85em;
            margin-left: 5px;
        }

        .d-none {
            display: none;
        }            
        
        .bg-lightblue { 
            background-color: rgb(230, 230, 255); 
        }
        
        .bg-white { 
            background-color: #ffffff; 
        }

        .obs-preview, .obs-completo {
            cursor: pointer;
            white-space: pre-line;
        }
    </style>
<header>
    <div class="header-left">
        <img src="{{ asset('images/logo.png') }}" alt="Logo TecnoPonto">
        <span style="color: #fff; font-weight: bold;">Total de Pedidos: {{ $totalPendentes }}</span>

        <div class="nav-buttons">
            <a href="{{ route('embalagem.index') }}" class="btn btn-primary">🚩 Pendentes</a>
            <a href="{{ route('embalagem.embalados') }}" class="btn btn-success">✅ Finalizados</a>
        </div>
    </div>

    <div class="header-right">
        <h1>Controle de Embalagem</h1>

        <form method="POST" action="{{ route('pedidos.atualizar') }}">
            @csrf
            <button type="submit" class="btn btn-success">🔄 Atualizar Pedidos Faturados</button>
        </form>
</div>


</header>
<form method="GET" action="{{ route('embalagem.index') }}" class="mb-3 d-flex align-items-center" style="gap: 10px;">
    <label for="codigo" class="mb-0">🔎 Consultar Pedido:</label>
    <input type="text" id="codigo" name="codigo" value="{{ $codigo }}" class="form-control" style="width: 120px;" placeholder="Ex: 91266">
    <button type="submit" class="btn btn-primary">Buscar</button>
    <a href="{{ route('embalagem.index') }}" class="btn btn-secondary">Limpar</a>
</form>
<table>
    <thead>
        <tr>
            <th>Pedido</th>
            <th>Descrição</th>
            <th>Qtd.</th>
            <th>Valor Est.</th>
            <th>Observações</th>
            <th>Início</th>
            <th>Fim</th>
            <th>Ações</th>
        </tr>
    </thead>
    <script>
        function toggleObs(element) {
            const preview = element.parentElement.querySelector('.obs-preview');
            const completo = element.parentElement.querySelector('.obs-completo');

            preview.classList.toggle('d-none');
            completo.classList.toggle('d-none');
        }
    </script>

    <tbody>
        @php
            $toggleColor = false;
        @endphp

        @foreach ($pedidos as $pedido)
            @php
                $toggleColor = !$toggleColor;
                $rowClass = $toggleColor ? 'bg-lightblue' : 'bg-white';
            @endphp

            <tr class="{{ $rowClass }}">
                <td rowspan="{{ $pedido->itens->count() }}">{{ $pedido->numero_pedido }}</td>
                <td>{{ $pedido->itens[0]->descricao ?? '-' }}</td>
                <td>{{ $pedido->itens[0]->quantidade ?? '-' }}</td>

                <td rowspan="{{ $pedido->itens->count() }}">
                    <form method="POST" action="{{ route('embalagem.valor', $pedido->id) }}">
                        @csrf
                        <input type="text" name="valor" value="{{ number_format($pedido->valor ?? 0, 2, ',', '.') }}" />
                        <button type="submit">💾</button>
                    </form>
                </td>
                <td rowspan="{{ $pedido->itens->count() }}">
                    @if ($pedido->observacoes)
                        @php
                            $obsLimite = 60;
                            $obsCompleta = $pedido->observacoes;
                            $obsResumida = Str::limit($obsCompleta, $obsLimite, '...');
                        @endphp

                        <span class="obs-preview" onclick="toggleObs(this)">
                            {{ $obsResumida }}
                            <span class="ver-mais">(ver mais)</span>
                        </span>
                        <span class="obs-completo d-none" onclick="toggleObs(this)">
                            {{ $obsCompleta }}
                            <span class="ver-mais">(ver menos)</span>
                        </span>
                    @else
                        -
                    @endif
                </td>
                <td rowspan="{{ $pedido->itens->count() }}">
                    {{ $pedido->inicio_embalagem ? \Carbon\Carbon::parse($pedido->inicio_embalagem)->format('d/m/Y H:i:s') : '-' }}
                </td>
                <td rowspan="{{ $pedido->itens->count() }}">
                    {{ $pedido->fim_embalagem ? \Carbon\Carbon::parse($pedido->fim_embalagem)->format('d/m/Y H:i:s') : '-' }}
                </td>

                <td rowspan="{{ $pedido->itens->count() }}">
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
                                    onclick="return confirm('Deseja reiniciar o controle de embalagem deste pedido?')">
                                Reiniciar
                            </button>
                        </form>
                    @endif
                </td>
            </tr>

            @foreach ($pedido->itens->slice(1) as $item)
                <tr class="{{ $rowClass }}">
                    <td>{{ $item->descricao }}</td>
                    <td>{{ $item->quantidade }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>
</script>
</body>
</html>
