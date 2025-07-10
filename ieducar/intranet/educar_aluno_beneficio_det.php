<?php

use App\Models\LegacyBenefit;

return new class extends clsDetalhe {
    public $titulo;

    public $cod_aluno_beneficio;

    public $ref_usuario_exc;

    public $ref_usuario_cad;

    public $nm_beneficio;

    public $desc_beneficio;

    public $data_cadastro;

    public $data_exclusao;

    public $ativo;

    public function Gerar()
    {
        $this->titulo = __('Aluno Beneficio - Detalhe');

        $this->cod_aluno_beneficio = $_GET['cod_aluno_beneficio'];

        $registro = LegacyBenefit::find($this->cod_aluno_beneficio)?->getAttributes();

        if (!$registro) {
            $this->simpleRedirect(url: 'educar_aluno_beneficio_lst.php');
        }

        if ($registro['cod_aluno_beneficio']) {
            $this->addDetalhe(detalhe: [__('Código Benefício'), "{$registro['cod_aluno_beneficio']}"]);
        }
        if ($registro['nm_beneficio']) {
            $this->addDetalhe(detalhe: [__('Benefício'), "{$registro['nm_beneficio']}"]);
        }
        if ($registro['desc_beneficio']) {
            $this->addDetalhe(detalhe: [__('Descrição'), nl2br(string: "{$registro['desc_beneficio']}")]);
        }

        $obj_permissao = new clsPermissoes;

        if ($obj_permissao->permissao_cadastra(int_processo_ap: 581, int_idpes_usuario: $this->pessoa_logada, int_soma_nivel_acesso: 3)) {
            $this->url_novo = 'educar_aluno_beneficio_cad.php';
            $this->url_editar = "educar_aluno_beneficio_cad.php?cod_aluno_beneficio={$registro['cod_aluno_beneficio']}";
        }

        $this->url_cancelar = 'educar_aluno_beneficio_lst.php';
        $this->largura = '100%';

        $this->breadcrumb(currentPage: __('Detalhe do benefício de alunos'), breadcrumbs: [
            url(path: 'intranet/educar_index.php') => __('Escola'),
        ]);
    }

    public function Formular()
    {
        $this->title = __('Benefício Aluno');
        $this->processoAp = '581';
    }
};
