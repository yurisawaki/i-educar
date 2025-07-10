@extends('layout.public')

@section('content')
    <h2>{{ __('Access your account') }}</h2>

    @if(config('legacy.config.url_cadastro_usuario'))
        <div>
            {{ __("Don't have an account?") }}
            <a target="_blank" href="{{ config('legacy.config.url_cadastro_usuario') }}" rel="noopener">
                {{ __('Create your account now') }}
            </a>.
        </div>
    @endif

    <form action="{{ Asset::get('login') }}" method="post" id="form-login">

        <label for="login">{{ __('Registration') }}:</label>
        <input type="text" name="login" id="login" value="{{ old('login') }}">

        <label for="password">{{ __('Password') }}:</label>
        <input type="password" name="password" id="password">
        <i class="fa fa-eye-slash" id="eye" onclick="showPassword()" onkeyup="showPassword()" aria-hidden="true"></i>

        <button id="form-login-submit" type="submit" class="submit">{{ __('Login') }}</button>

        <div class="remember">
            <a href="{{ route('password.request') }}">{{ __('Forgot your password?') }}</a>
        </div>

    </form>

    <script>
        function showPassword() {
            var input = document.getElementById("password");
            var eye = document.getElementById("eye");

            if (input.type === "password") {
                input.type = "text";
                eye.classList.remove("fa-eye-slash");
                eye.classList.add("fa-eye");
            } else {
                input.type = "password";
                eye.classList.remove("fa-eye");
                eye.classList.add("fa-eye-slash");
            }
        }
    </script>

    @if (config('legacy.app.recaptcha_v3.public_key') && config('legacy.app.recaptcha_v3.private_key'))
        <script src="https://www.google.com/recaptcha/api.js?render={{config('legacy.app.recaptcha_v3.public_key')}}"></script>
        <script src="{{ Asset::get('/intranet/scripts/jquery/jquery-1.8.3.min.js') }}"></script>

        <script>
            let grecaptchaKey = "{{config('legacy.app.recaptcha_v3.public_key')}}";
            let form = $('#form-login');

            grecaptcha.ready(function () {
                form.submit(function (e) {
                    e.preventDefault();
                    grecaptcha.execute(grecaptchaKey, { action: 'submit' })
                        .then((token) => {
                            let input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'grecaptcha';
                            input.value = token;

                            form.append(input);

                            $(this).unbind('submit').submit();
                        });
                });
            });
        </script>
    @endif
@endsection