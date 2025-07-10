<?php

use App\Models\LegacyAbandonmentType;

return new class extends clsCadastro {
    public $pessoa_logada;

    public $cod_abandono_tipo;

    public $ref_usuario_exc;

    public $ref_usuario_cad;

    public $nome;

    public $data_cadastro;

    public $data_exclusao;

    public $ativo;

    public $ref_cod_instituicao;

    public function Inicializar()
    {
        $retorno = __('Novo');

        $this->cod_abandono_tipo = $_GET['cod_abandono_tipo'];

        $obj_permissoes = new clsPermissoes;
        $obj_permissoes->permissao_cadastra(
            int_processo_ap: 950,
            int_idpes_usuario: $this->pessoa_logada,
            int_soma_nivel_acesso: 7,
            str_pagina_redirecionar: 'educar_abandono_tipo_lst.php'
        );

        if (is_numeric($this->cod_abandono_tipo)) {
            $registro = LegacyAbandonmentType::find($this->cod_abandono_tipo)?->getAttributes();
            if ($registro) {
                foreach ($registro as $campo => $val) {
                    $this->$campo = $val;
                }

                $this->fexcluir = $obj_permissoes->permissao_excluir(
                    int_processo_ap: 950,
                    int_idpes_usuario: $this->pessoa_logada,
                    int_soma_nivel_acesso: 7
                );
                $retorno = __('Editar');
            }
        }

        $this->url_cancelar =
            ($retorno == __('Editar'))
            ? "educar_abandono_tipo_det.php?cod_abandono_tipo={$registro['cod_abandono_tipo']}"
            : 'educar_abandono_tipo_lst.php';
        $this->nome_url_cancelar = __('Cancelar');

        $nomeMenu = ($retorno == __('Editar')) ? $retorno : __('Cadastrar');

        $this->breadcrumb(
            currentPage: $nomeMenu . ' ' . __('tipo de abandono'),
            breadcrumbs: [
                url('intranet/educar_index.php') => __('Escola'),
            ]
        );

        return $retorno;
    }

    public function Gerar()
    {
        $this->campoOculto(nome: 'cod_abandono_tipo', valor: $this->cod_abandono_tipo);

        $obrigatorio = true;
        include 'include/pmieducar/educar_campo_lista.php';

        $this->campoTexto(
            nome: 'nome',
            campo: __('Motivo Abandono'),
            valor: $this->nome,
            tamanhovisivel: 30,
            tamanhomaximo: 255,
            obrigatorio: true
        );
    }

    public function Novo()
    {
        $abandono = new LegacyAbandonmentType;
        $abandono->ref_usuario_cad = $this->pessoa_logada;
        $abandono->nome = $this->nome;
        $abandono->ref_cod_instituicao = $this->ref_cod_instituicao;

        if ($abandono->save()) {
            $this->mensagem .= __('Cadastro efetuado com sucesso.<br>');
            $this->simpleRedirect('educar_abandono_tipo_lst.php');
        }

        $this->mensagem = __('Cadastro não realizado.<br>');

        return false;
    }

    public function Editar()
    {
        $abandono = LegacyAbandonmentType::find($this->cod_abandono_tipo);
        $abandono->ref_usuario_exc = $this->pessoa_logada;
        $abandono->nome = $this->nome;
        $abandono->ativo = 1;
        $abandono->ref_cod_instituicao = $this->ref_cod_instituicao;

        if ($abandono->save()) {
            $this->mensagem .= __('Edição efetuada com sucesso.<br>');

            $this->simpleRedirect('educar_abandono_tipo_lst.php');
        }

        $this->mensagem = __('Edição não realizada.<br>');

        return false;
    }

    public function Excluir()
    {
        $abandono = LegacyAbandonmentType::find($this->cod_abandono_tipo);
        $abandono->ativo = 0;
        $abandono->ref_usuario_exc = $this->pessoa_logada;

        if ($abandono->save()) {
            $this->mensagem .= __('Exclusão efetuada com sucesso.<br>');
            $this->simpleRedirect('educar_abandono_tipo_lst.php');
        }

        $this->mensagem = __('Exclusão não realizada.<br>');

        return false;
    }

    public function Formular()
    {
        $this->title = __('Motivo Abandono');
        $this->processoAp = '950';
    }
};
