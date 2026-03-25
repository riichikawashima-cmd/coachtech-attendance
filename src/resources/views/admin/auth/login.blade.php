@extends('layouts.app')

@section('title', '管理者ログイン')

@section('css')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endsection

@section('content')
<div class="register">
    <h1 class="register__title">管理者ログイン</h1>

    <form method="POST" action="/admin/login" novalidate>
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
            管理者ログインする
        </button>
    </form>
</div>
@endsection