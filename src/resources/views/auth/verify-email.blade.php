@extends('layouts/login_register')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/verify-email.css') }}">
@endsection

@section('content')
<div class="verify-email">
  <p>登録していただたメールアドレスに認証メールを送付しました。</p>
  <p>メール認証を完了してください</p>

  @if (session('message'))
      <p style="color: green;">{{ session('message') }}</p>
  @endif

  <button class="btn-certification">認証はこちらから</button>
</form>
  <form method="POST" action="{{ route('verification.send') }}">
      @csrf
      <button type="submit" class="btn-resend">認証メールを再送する</button>
  </form>
</div>
@endsection