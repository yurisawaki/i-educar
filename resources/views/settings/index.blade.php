@inject('settingView', 'App\Support\View\SettingView')
@extends('layout.default')

@push('styles')
    <link rel="stylesheet" type="text/css" href="{{ Asset::get('css/ieducar.css') }}"/>
    <style>
        .table-default tbody tr td {
            padding: 8px;
            font-size: 14px;
        }
    </style>
@endpush

@section('content')

    <form id="formcadastro" action="" method="post">
        <table class="table-default" width="100%" border="0" cellpadding="2" cellspacing="0" role="presentation">
            <tbody>
            <tr>
                <td class="titulo-tabela-listagem" colspan="2" height="24"><b>Configurações iniciais</b></td>
            </tr>
            @foreach($categories as $category)
                <tr>
                    <td colspan="2" height="24">
                        <strong>{{$category->name}}</strong>
                    </td>
                </tr>
                @foreach($category->settings as $field)
                    {!! $settingView->makeInput(
                        $field->id,
                        $field->description,
                        $field->type,
                        $field->key,
                        $field->value,
                        $category->enabled,
                        $field->hint,
                        $field->maxlength
                    ) !!}
                @endforeach
            @endforeach
            </tbody>
        </table>
        <div class="separator"></div>

        <div style="text-align: center">
            <button class="btn-green" type="submit">Salvar</button>
        </div>

    </form>

@endsection
