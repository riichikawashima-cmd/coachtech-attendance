@extends('layouts.app')

@section('title', 'メール認証')

@section('css')
<link rel="stylesheet" href="{{ asset('css/verify_email.css') }}">
@endsection

@section('content')
<div class="verify-email">
    <p class="verify-email__text">
        登録していただいたメールアドレスに認証メールを送信しました。<br>
        メール認証を完了してください。
    </p>

    <a href="http://localhost:8025" class="verify-email__mailhog" target="_blank" rel="noopener noreferrer">
        認証はこちらから
    </a>

    <form method="POST" action="{{ route('verification.send') }}" class="verify-email__resend">
        @csrf
        <button type="submit">認証メールを再送する</button>
    </form>
</div>
@endsection