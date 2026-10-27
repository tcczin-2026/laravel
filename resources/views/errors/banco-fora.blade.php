{{-- Mostrada quando o MySQL está fora do ar (não usa o layout, que depende do banco) --}}
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="15">
    <title>Banco de dados fora do ar - Colecao de Games</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/theme.css') }}" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center p-3">
    <div class="card card-body text-center" style="max-width: 480px;">
        <div style="font-size: 2.5rem;">&#128268;</div>
        <h1 class="h4 mt-2">Banco de dados fora do ar</h1>
        <p class="text-muted mb-3">
            O sistema não conseguiu se conectar ao MySQL.
            Nenhum dado foi perdido: assim que o banco voltar, tudo funciona de novo.
        </p>
        <div class="alert alert-danger text-start small mb-3">
            <strong>Como resolver:</strong> abra o painel do XAMPP e clique em
            <strong>Start</strong> no <strong>MySQL</strong>.
        </div>
        <a href="{{ url()->current() }}" class="btn btn-primary">Tentar de novo</a>
        <p class="small text-muted mt-3 mb-0">Esta página tenta sozinha a cada 15 segundos.</p>
    </div>
</body>
</html>
