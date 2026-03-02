@extends('layouts.app')

@section('content')
<div style="text-align:center; margin-top:100px;">
    <h2>メール認証</h2>
    <p>登録したメールアドレスに認証リンクを送信しました。</p>
    <p>メールをご確認ください。</p>

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit">認証メールを再送する</button>
    </form>
</div>
@endsection
