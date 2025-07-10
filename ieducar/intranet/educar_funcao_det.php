<?php

use App\Models\LegacyRole;

return new class extends clsDetalhe {
    /**
     * Titulo no topo da pagina
     *
     * @var int
     */
    public $titulo;

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

    public function Gerar()
    {
        $this->titulo = __('Função - Detalhe');

        $this->cod_funcao = $_GET['cod_funcao'];
        $this->ref_cod_instituicao = $_GET['ref_cod_instituicao'];

        $registro = LegacyRole::find($this->cod_funcao)?->getAttributes();
        if (!$registro) {
            $this->simpleRedirect('educar_fonte_lst.php');
        }

        $obj_permissoes = new clsPermissoes;
        $nivel_usuario = $obj_permissoes->nivel_acesso($this->pessoa_logada);

        if ($nivel_usuario == 1) {
            if ($registro['ref_cod_instituicao']) {
                $this->addDetalhe([__('Instituição'), "{$registro['ref_cod_instituicao']}"]);
            }
        }
        if ($registro['cod_funcao']) {
            $this->addDetalhe([__('Função'), "{$registro['cod_funcao']}"]);
        }
        if ($registro['nm_funcao']) {
            $this->addDetalhe([__('Nome Função'), "{$registro['nm_funcao']}"]);
        }
        if ($registro['abreviatura']) {
            $this->addDetalhe([__('Abreviatura'), "{$registro['abreviatura']}"]);
        }

        $opcoes = [
            '1' => __('Sim'),
            '0' => __('Não'),
        ];

        if (is_numeric($registro['professor'])) {
            $this->addDetalhe([__('Professor'), "{$opcoes[$registro['professor']]}"]);
        }

        if ($obj_permissoes->permissao_cadastra(634, $this->pessoa_logada, 3)) {
            $this->url_novo = 'educar_funcao_cad.php';
            $this->url_editar = "educar_funcao_cad.php?cod_funcao={$registro['cod_funcao']}";
        }

        $this->url_cancelar = 'educar_funcao_lst.php';
        $this->largura = '100%';

        $this->breadcrumb(__('Detalhe da função'), [
            url('intranet/educar_servidores_index.php') => __('Servidores'),
        ]);
    }

    public function Formular()
    {
        $this->title = __('Servidores - Funções do servidor');
        $this->processoAp = '634';
    }
};
