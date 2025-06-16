<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Pedidos Finalizados</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
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
        }

        .header-left img {
            height: 200px;
            margin-bottom: 10px;
        }

        .nav-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            width: 100%;
        }

        .nav-buttons a {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: bold;
            text-decoration: none;
            color: #fff;
        }

        .btn-outline-primary {
            background-color: #007BFF;
        }

        .btn-outline-success {
            background-color: #28a745;
        }

        .container {
            padding: 40px;
        }

        h1 {
            color: #0d1e40;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        th, td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        th {
            background-color: #007BFF;
            color: #fff;
            font-weight: bold;
            font-size: 14px;
        }

        td {
            font-size: 13px;
            color: #333;
        }

        td[colspan] {
            text-align: center;
            color: #777;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-left">
            <img src="/images/logo.png" alt="Logo TecnoPonto">
            <div class="nav-buttons">
                <a href="{{ route('embalagem.index') }}" class="btn btn-outline-primary">🚩 Pendentes</a>
                <a href="{{ route('embalagem.embalados') }}" class="btn btn-outline-success">✅ Finalizados</a>
            </div>
        </div>
    </header>

    <div class="container">
        <h1>Pedidos Finalizados</h1>

        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Descrição</th>
                    <th>Quantidade</th>
                    <th>Valor Estimado</th>
                    <th>Observações</th>
                    <th>Início</th>
                    <th>Fim</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pedidos as $pedido)
                    <tr>
                        <td>{{ $pedido->codigo_pedido }}</td>
                        <td>{{ $pedido->descricao }}</td>
                        <td>{{ $pedido->quantidade }}</td>
                        <td>R$ {{ number_format($pedido->valor ?? 0, 2, ',', '.') }}</td>
                        <td style="white-space: pre-wrap;">{{ $pedido->observacoes }}</td>
                        <td>{{ \Carbon\Carbon::parse($pedido->inicio_embalagem)->format('d/m/Y H:i:s') }}</td>
                        <td>{{ \Carbon\Carbon::parse($pedido->fim_embalagem)->format('d/m/Y H:i:s') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">Nenhum pedido finalizado encontrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
