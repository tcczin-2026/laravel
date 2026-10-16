<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Colecao de Games</title>

    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>

<body>

    <div class="ring">
        <i></i>
        <i></i>
        <i></i>
        <form class="login" method="post" action="{{ route('login.store') }}">
            @csrf
            <h2>Login</h2>

            @if (session('status'))
                <div class="sucesso">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="erro">{{ $errors->first() }}</div>
            @endif

            <div class="inputBx">
                <input type="email" name="email" placeholder="E-mail" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="inputBx">
                <input type="password" name="password" placeholder="Senha" required>
            </div>
            <div class="inputBx">
                <input type="submit" value="Entrar">
            </div>
            <div class="links">
                <label><input type="checkbox" name="lembrar" value="1"> Lembrar de mim</label>
                <a href="{{ route('password.request') }}">Esqueceu a senha?</a>
            </div>
        </form>
    </div>

</body>
</html>
