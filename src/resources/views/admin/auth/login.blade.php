@extends('layouts.app')

@section('title', '管理者ログイン')

@section('css')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endsection

@section('content')
<div class="register">
    <h1 class="register__title">管理者ログイン</h1>

    @if ($errors->any())
    <div class="register__error">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="/admin/login">
        @csrf

        <div class="register__group">
            <label>メールアドレス</label>
            <input type="email" name="email" value="{{ old('email') }}">
        </div>

        <div class="register__group">
            <label>パスワード</label>
            <input type="password" name="password">
        </div>

        <button type="submit" class="register__button">
            管理者ログインする
        </button>
    </form>
</div>
@endsection