<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova senha - Colecao de Games</title>

    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>

<body>

    <div class="ring">
        <i></i>
        <i></i>
        <i></i>
        <form class="login" method="post" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <h2>Nova senha</h2>

            @if ($errors->any())
                <div class="erro">{{ $errors->first() }}</div>
            @endif

            <div class="inputBx">
                <input type="email" name="email" placeholder="E-mail" value="{{ old('email', $email) }}" required>
            </div>
            <div class="inputBx">
                <input type="password" name="password" placeholder="Nova senha (mín. 6)" minlength="6" required autofocus>
            </div>
            <div class="inputBx">
                <input type="password" name="password_confirmation" placeholder="Repita a nova senha" required>
            </div>
            <div class="inputBx">
                <input type="submit" value="Salvar senha">
            </div>
            <div class="links">
                <a href="{{ route('login') }}">&larr; Voltar ao login</a>
            </div>
        </form>
    </div>

</body>
</html>
