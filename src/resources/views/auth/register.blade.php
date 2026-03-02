@extends('layouts.app')

@section('title', '会員登録')

@section('css')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endsection

@section('content')
<div class="register-wrapper">
    <div class="register">
        <h2 class="register__title">会員登録</h2>

        @if ($errors->any())
        <div class="register__error">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="register__form">
            @csrf

            <div class="register__group">
                <label>名前</label>
                <input type="text" name="name" value="{{ old('name') }}">
            </div>

            <div class="register__group">
                <label>メールアドレス</label>
                <input type="email" name="email" value="{{ old('email') }}">
            </div>

            <div class="register__group">
                <label>パスワード</label>
                <input type="password" name="password">
            </div>

            <div class="register__group">
                <label>パスワード確認</label>
                <input type="password" name="password_confirmation">
            </div>

            <button type="submit" class="register__button">
                登録する
            </button>
        </form>

        <a href="{{ route('login') }}" class="register__login-link">
            ログインはこちら
        </a>
    </div>
</div>
@endsection