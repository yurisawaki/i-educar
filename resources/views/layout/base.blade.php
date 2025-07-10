<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}"> {{-- Use a localidade dinâmica --}}

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="{{ url('favicon.ico') }}" />
    <title>
        @if(isset($title)) {!! html_entity_decode($title) !!} - @endif
        {{ html_entity_decode(config('legacy.app.entity.name')) }} - i-Educar
    </title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans">
    <link rel="stylesheet" href="{{ Asset::get('css/vue-multiselect.min.css') }}">
    <link rel="stylesheet" href="{{ Asset::get('intranet/styles/font-awesome.css') }}">
    <link rel="stylesheet" href="{{ Asset::get('css/base.css') }}">
    @stack('styles')
</head>

<body>
    <div class="ieducar-container">
        <header class="ieducar-header">
            <div class="ieducar-header-logo">
                <h1><a href="{{ Asset::get('/') }}">i-Educar</a></h1>
            </div>
            <div class="ieducar-header-links">
                <div class="dropdown">
                    <div class="dropbtn">{{ $loggedUser->name }}</div>
                    <div class="dropdown-content">
                        <a href="{{ Asset::get('intranet/agenda.php') }}">{{ __('Agenda') }}</a>
                        <a href="{{ Asset::get('intranet/meusdados.php') }}">{{ __('Meus dados') }}</a>
                        <a href="#" id="reloadPermissionsLink">{{ __('Recarregar') }}</a> {{-- Tradução --}}
                    </div>
                </div>
                <a href="{{ Asset::get('intranet/meusdados.php') }}" class="avatar" title="{{ __('Meus dados') }}">
                    <img height="35" src="{{ Asset::get('intranet/imagens/user-perfil.png') }}"
                        alt="{{ __('Perfil') }}">
                </a>
                <a href="#" class="notifications">
                    <img alt="{{ __('Notificação') }}" id="notificacao"
                        src="{{ Asset::get('intranet/imagens/icon-nav-notifications.png') }}">
                </a>
            </div>
        </header>

        <div class="ieducar-content">
            <div class="ieducar-sidebar">
                @include('layout.menu')
            </div>
            <div class="ieducar-main">
                @include('layout.topmenu')
                <div class="ieducar-main-content">
                    @include('layout.breadcrumb')
                    @yield('content')
                </div>
            </div>
        </div>

        <footer class="ieducar-footer">
            @include('layout.footer')
        </footer>
    </div>

    @include('layout.vue')
    @stack('end')

    @push('scripts')
        <script>
            // Função para corrigir permissões e recarregar a página
            function fixarPermissaoERecarregar() {
                console.log("Função chamada");
                fetch("{{ route('fix.log') }}", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('meta[name=csrf-token]').content,
                        "Accept": "application/json",
                        "Content-Type": "application/json"
                    }
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            alert("{{ __('Permissões corrigidas com sucesso.') }}");
                            location.reload();
                        } else {
                            alert("{{ __('Erro:') }} " + data.message);
                        }
                    })
                    .catch(error => {
                        console.error("{{ __('Erro na requisição:') }}", error);
                        alert("{{ __('Erro ao corrigir permissões.') }}");
                    });
            }

            // Adicionar o evento de clique programaticamente
            document.addEventListener("DOMContentLoaded", function () {
                const reloadButton = document.getElementById("reloadPermissionsLink");
                if (reloadButton) {
                    reloadButton.addEventListener("click", function (e) {
                        e.preventDefault(); // Previne o comportamento padrão do link
                        fixarPermissaoERecarregar(); // Chama a função
                    });
                }
            });
        </script>
    @endpush

    @stack('scripts')
</body>

</html>