<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esqueci minha senha - Colecao de Games</title>

    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>

<body>

    <div class="ring">
        <i></i>
        <i></i>
        <i></i>
        <form class="login" method="post" action="{{ route('password.email') }}">
            @csrf
            <h2>Esqueci a senha</h2>

            @if (session('status'))
                <div class="sucesso">{{ session('status') }}</div>
            @elseif ($errors->any())
                <div class="erro">{{ $errors->first() }}</div>
            @else
                <p class="dica">Informe seu e-mail e enviaremos um link para criar uma nova senha.</p>
            @endif

            <div class="inputBx">
                <input type="email" name="email" placeholder="E-mail" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="inputBx">
                <input type="submit" value="Enviar link">
            </div>
            <div class="links">
                <a href="{{ route('login') }}">&larr; Voltar ao login</a>
            </div>
        </form>
    </div>

</body>
</html>
