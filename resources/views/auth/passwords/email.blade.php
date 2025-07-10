@extends('layout.public')

@section('content')
    <h2>{{ __('Reset Password') }}</h2>

    <form action="{{ route('password.email') }}" method="post">
        @csrf

        <label for="login">{{ __('Registration') }}:</label>
        <input type="text" name="login" id="login" value="{{ old('login') }}">

        <button type="submit" class="submit">{{ __('Reset Password') }}</button>

        <div class="remember">
            <a href="{{ Asset::get('login') }}">{{ __('Login') }}?</a>
        </div>

    </form>
@endsection