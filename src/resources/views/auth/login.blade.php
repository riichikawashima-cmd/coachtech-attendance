@extends('layouts.app')

@section('title', 'ログイン')

@section('css')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endsection

@section('content')
<div class="register">
    <h1 class="register__title">ログイン</h1>

    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        <div class="register__group">
            <label>メールアドレス</label>
            <input type="email" name="email" value="{{ old('email') }}">
            @error('email')
            <p class="register__error-message">{{ $message }}</p>
            @enderror
        </div>

        <div class="register__group">
            <label>パスワード</label>
            <input type="password" name="password">
            @error('password')
            <p class="register__error-message">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="register__button">
            ログインする
        </button>
    </form>

    <a href="{{ route('register') }}" class="register__login-link">
        会員登録はこちら
    </a>
</div>
@endsection