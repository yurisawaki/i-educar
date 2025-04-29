<!DOCTYPE html>
<html lang="pt" class="no-js">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="-1" />
    <link rel="shortcut icon" href="{{ url('favicon.ico') }}" />
    <title>@if(isset($title)) {!! html_entity_decode($title) !!} - @endif
        {{ html_entity_decode(config('legacy.app.entity.name')) }} - i-Educar
    </title>

    <script>
        dataLayer = [{
            'slug': '{{$config['app']['database']['dbname']}}',
            'user_id': '{{$loggedUser->personId}}',
            'user_name': '{{$loggedUser->name}}',
            'user_email': '{{$loggedUser->email}}',
            'user_role': '{{$loggedUser->role}}',
            'user_created_at': parseInt('{{$loggedUser->created_at}}', 10),
            'institution': '{{ $loggedUser->institution }}',
            'city': '{{ $loggedUser->city }}',
            'state': '{{ $loggedUser->state }}',
            'students_count': '{{ $loggedUser->students_count }}',
            'teachers_count': '{{ $loggedUser->teachers_count }}',
            'classes_count': '{{ $loggedUser->classes_count }}',
        }];
        window.useEcho = '{{ config('broadcasting.default') }}' !== '';
    </script>

    @if(!empty($config['app']['gtm']['id']))
        <!-- Google Tag Manager -->
        <script>(function (w, d, s, l, i) {
                w[l] = w[l] || [];
                w[l].push({
                    'gtm.start':
                        new Date().getTime(), event: 'gtm.js'
                });
                var f = d.getElementsByTagName(s)[0],
                    j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : '';
                j.async = true;
                j.src =
                    'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
                f.parentNode.insertBefore(j, f);
            })(window, document, 'script', 'dataLayer', '{{$config['app']['gtm']['id']}}');</script>
        <!-- End Google Tag Manager -->
    @endif

    <link rel=stylesheet type='text/css' href='{{ Asset::get('/intranet/styles/main.css') }}' />
    <link rel=stylesheet type='text/css' href='{{ Asset::get('/intranet/styles/styles.css') }}' />
    <link rel=stylesheet type='text/css' href='{{ Asset::get('/intranet/styles/novo.css') }}' />
    <link rel=stylesheet type='text/css' href='{{ Asset::get('/intranet/styles/menu.css') }}' />
    <link rel=stylesheet type='text/css' href='{{ Asset::get('/intranet/styles/font-awesome.css') }}' />
    <!--link rel=stylesheet type='text/css' href='{{ Asset::get('styles/reset.css') }}'/>
    <link rel=stylesheet type='text/css' href='{{ Asset::get('styles/portabilis.css') }}' /-->
    <link rel=stylesheet type='text/css' href='{{ Asset::get('/intranet/styles/min-portabilis.css') }}' />
    <link rel=stylesheet type='text/css' href='{{ Asset::get('/intranet/styles/mytdt.css') }}' />
    <link rel=stylesheet type='text/css' href='{{ Asset::get('/intranet/styles/jquery.modal.css') }}' />

    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB9DNFdkfu535bE04v6p-CCRUUFw69kpYI"
        type="text/javascript" charset="utf-8"></script>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">

    <script>(function (e, t, n) {
            var r = e.querySelectorAll("html")[0];
            r.className = r.className.replace(/(^|\s)no-js(\s|$)/, "$1js$2")
        })(document, window, 0);</script>

    <script type="text/javascript" src="{{ Asset::get("/intranet/scripts/padrao.js") }} "></script>
    <script type="text/javascript" src="{{ Asset::get("/intranet/scripts/novo.js") }} "></script>
    <script type="text/javascript" src="{{ Asset::get("/intranet/scripts/dom.js") }} "></script>
    <script type="text/javascript" src="{{ Asset::get("/intranet/scripts/ied/forms.js") }} "></script>
    <script type="text/javascript" src="{{ Asset::get("/intranet/scripts/ied/phpjs.js") }} "></script>

    <script type="text/javascript" src="{{ Asset::get("/intranet/scripts/jquery/jquery-1.8.3.min.js") }} "></script>
    <script type="text/javascript" src="{{ Asset::get("/intranet/scripts/jquery/jquery.modal.min.js") }} "></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prototype/1.7.1.0/prototype.min.js"
        integrity="sha512-BfwTGy/vhB1IOMlnxjnHLDQFX9FAidk1uYzXB6JOj9adeMoKlO3Bi3rZGGOrYfCOhBMZggeXTBmmdkfscYOQ/w=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script type="text/javascript" src="{{ Asset::get("/intranet/scripts/jquery.mask.min.js") }} "></script>

    <link rel=stylesheet type='text/css' href='{{ Asset::get('/intranet/styles/custom.css') }}' />
</head>

<body>

    @if(!empty($config['app']['gtm']['id']))
        <!-- Google Tag Manager (noscript) -->
        <noscript>
            <iframe src="https://www.googletagmanager.com/ns.html?id={{$config['app']['gtm']['id']}}" height="0" width="0"
                style="display:none;visibility:hidden" title="Google Tag Manager"></iframe>
        </noscript>
        <!-- End Google Tag Manager (noscript) -->
    @endif

    @yield('content')

    <script type='text/javascript'
        src='{{ Asset::get('/vendor/legacy/Portabilis/Assets/Javascripts/Utils.js') }}'></script>
    <script type='text/javascript'>(function ($) {
            $(document).ready(function () {
                fixupFieldsWidth();
            });
        })(jQuery);</script>

    <script src="{{ Asset::get("/intranet/scripts/custom-file-input.js") }}"></script>
    <script type="text/javascript" src="{{ Asset::get("/intranet/scripts/select2/select2.full.min.js") }}"></script>
    <script type="text/javascript" src="{{ Asset::get("/intranet/scripts/select2/pt-BR.js") }}"></script>
    <link type="text/css" rel="stylesheet" href="{{ Asset::get("/intranet/scripts/select2/select2.min.css") }}" />