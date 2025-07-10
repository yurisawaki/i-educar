<?php

use App\Models\LegacyRole;

return new class extends clsCadastro {
    public $pessoa_logada;

    public $cod_funcao;

    public $ref_usuario_exc;

    public $ref_usuario_cad;

    public $nm_funcao;

    public $abreviatura;

    public $professor;

    public $data_cadastro;

    public $data_exclusao;

    public $ativo;

    public $ref_cod_instituicao;

    public function Inicializar()
    {
        $retorno = __('Novo');

        $this->cod_funcao = $_GET['cod_funcao'];

        $obj_permissoes = new clsPermissoes;
        $obj_permissoes->permissao_cadastra(
            int_processo_ap: 634,
            int_idpes_usuario: $this->pessoa_logada,
            int_soma_nivel_acesso: 3,
            str_pagina_redirecionar: 'educar_funcao_lst.php'
        );

        if (is_numeric($this->cod_funcao)) {
            $registro = LegacyRole::find($this->cod_funcao)?->getAttributes();
            if ($registro) {
                foreach ($registro as $campo => $val) {
                    $this->$campo = $val;
                }

                if (
                    $obj_permissoes->permissao_excluir(
                        int_processo_ap: 634,
                        int_idpes_usuario: $this->pessoa_logada,
                        int_soma_nivel_acesso: 3
                    )
                ) {
                    $this->fexcluir = true;
                }
                $retorno = __('Editar');
            }

            if ($this->professor == '0') {
                $this->professor = 'N';
            } elseif ($this->professor == '1') {
                $this->professor = 'S';
            }
        }
        $this->url_cancelar = ($retorno == __('Editar'))
            ? "educar_funcao_det.php?cod_funcao={$registro['cod_funcao']}"
            : 'educar_funcao_lst.php';
        $this->nome_url_cancelar = __('Cancelar');

        $nomeMenu = ($retorno == __('Editar')) ? $retorno : __('Cadastrar');

        $this->breadcrumb(
            currentPage: $nomeMenu . ' ' . __('função'),
            breadcrumbs: [
                url('intranet/educar_servidores_index.php') => __('Servidores'),
            ]
        );

        return $retorno;
    }

    public function Gerar()
    {
        // primary keys
        $this->campoOculto(nome: 'cod_funcao', valor: $this->cod_funcao);

        $obrigatorio = true;
        include 'include/pmieducar/educar_campo_lista.php';

        // text
        $this->campoTexto(
            nome: 'nm_funcao',
            campo: __('Função'),
            valor: $this->nm_funcao,
            tamanhovisivel: 30,
            tamanhomaximo: 255,
            obrigatorio: true
        );
        $this->campoTexto(
            nome: 'abreviatura',
            campo: __('Abreviatura'),
            valor: $this->abreviatura,
            tamanhovisivel: 30,
            tamanhomaximo: 30,
            obrigatorio: true
        );
        $opcoes = [
            '' => __('Selecione'),
            'S' => __('Sim'),
            'N' => __('Não'),
        ];

        $this->campoLista(
            nome: 'professor',
            campo: __('Professor'),
            valor: $opcoes,
            default: $this->professor
        );
    }

    public function Novo()
    {
        $obj_permissoes = new clsPermissoes;
        $obj_permissoes->permissao_cadastra(
            int_processo_ap: 634,
            int_idpes_usuario: $this->pessoa_logada,
            int_soma_nivel_acesso: 3,
            str_pagina_redirecionar: 'educar_funcao_lst.php'
        );

        if ($this->professor == 'N') {
            $this->professor = '0';
        } elseif ($this->professor == 'S') {
            $this->professor = '1';
        }

        $obj = new LegacyRole;
        $obj->nm_funcao = $this->nm_funcao;
        $obj->abreviatura = $this->abreviatura;
        $obj->professor = $this->professor;
        $obj->ref_cod_instituicao = $this->ref_cod_instituicao;
        $obj->ref_usuario_cad = $this->pessoa_logada;

        if ($obj->save()) {
            $this->mensagem .= __('Cadastro efetuado com sucesso.') . '<br>';
            $this->simpleRedirect('educar_funcao_lst.php');
        }

        $this->mensagem = __('Cadastro não realizado.') . '<br>';

        return false;
    }

    public function Editar()
    {
        if ($this->professor == 'N') {
            $this->professor = '0';
        } elseif ($this->professor == 'S') {
            $this->professor = '1';
        }

        $obj_permissoes = new clsPermissoes;
        $obj_permissoes->permissao_cadastra(
            int_processo_ap: 634,
            int_idpes_usuario: $this->pessoa_logada,
            int_soma_nivel_acesso: 3,
            str_pagina_redirecionar: 'educar_funcao_lst.php'
        );

        $obj = LegacyRole::find($this->cod_funcao);
        $obj->nm_funcao = $this->nm_funcao;
        $obj->abreviatura = $this->abreviatura;
        $obj->professor = $this->professor;
        $obj->ref_cod_instituicao = $this->ref_cod_instituicao;
        $obj->ref_usuario_exc = $this->pessoa_logada;

        if ($obj->save()) {
            $this->mensagem .= __('Edição efetuada com sucesso.') . '<br>';
            $this->simpleRedirect('educar_funcao_lst.php');
        }

        $this->mensagem = __('Edição não realizada.') . '<br>';

        return false;
    }

    public function Excluir()
    {
        $obj_permissoes = new clsPermissoes;
        $obj_permissoes->permissao_excluir(
            int_processo_ap: 634,
            int_idpes_usuario: $this->pessoa_logada,
            int_soma_nivel_acesso: 3,
            str_pagina_redirecionar: 'educar_funcao_lst.php'
        );

        $obj = LegacyRole::find($this->cod_funcao);

        if ($obj->delete()) {
            $this->mensagem .= __('Exclusão efetuada com sucesso.') . '<br>';
            $this->simpleRedirect('educar_funcao_lst.php');
        }

        $this->mensagem = __('Exclusão não realizada.') . '<br>';

        return false;
    }

    public function Formular()
    {
        $this->title = __('Servidores -  Funções do servidor');
        $this->processoAp = '634';
    }
};
