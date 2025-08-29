<?php

use App\Models\LegacyDisciplinaryOccurrenceType;

return new class extends clsListagem {
    public $pessoa_logada;

    public $titulo;

    public $limite;

    public $offset;

    public $cod_tipo_ocorrencia_disciplinar;

    public $ref_usuario_exc;

    public $ref_usuario_cad;

    public $nm_tipo;

    public $descricao;

    public $max_ocorrencias;

    public $data_cadastro;

    public $data_exclusao;

    public $ativo;

    public $ref_cod_instituicao;

    public function Gerar()
    {
        $this->titulo = __('Tipo Ocorrência Disciplinar - Listagem');

        foreach ($_GET as $var => $val) {
            $this->$var = ($val === '') ? null : $val;
        }

        $lista_busca = [
            __('Tipo Ocorrência Disciplinar'),
            __('Máximo Ocorrências'),
        ];

        $obj_permissoes = new clsPermissoes;
        $nivel_usuario = $obj_permissoes->nivel_acesso($this->pessoa_logada);
        if ($nivel_usuario == 1) {
            $lista_busca[] = __('Instituição');
        }

        $this->addCabecalhos($lista_busca);

        include 'include/pmieducar/educar_campo_lista.php';

        $this->campoTexto('nm_tipo', __('Tipo Ocorrência Disciplinar'), $this->nm_tipo, 30, 255, false);

        $this->limite = 20;
        $this->offset = ($_GET["pagina_{$this->nome}"]) ? $_GET["pagina_{$this->nome}"] * $this->limite - $this->limite : 0;

        $query = LegacyDisciplinaryOccurrenceType::query()
            ->where('ativo', 1)
            ->orderBy('nm_tipo', 'ASC');

        if (is_string($this->nm_tipo)) {
            $query->where('nm_tipo', 'ilike', '%' . $this->nm_tipo . '%');
        }

        if (is_numeric($this->ref_cod_instituicao)) {
            $query->where('ref_cod_instituicao', $this->ref_cod_instituicao);
        }

        $result = $query->paginate($this->limite, '*', 'pagina_' . $this->nome);

        $lista = $result->items();
        $total = $result->total();

        if (is_array($lista) && count($lista)) {
            foreach ($lista as $registro) {
                $obj_cod_instituicao = new clsPmieducarInstituicao($registro['ref_cod_instituicao']);
                $obj_cod_instituicao_det = $obj_cod_instituicao->detalhe();
                $registro['ref_cod_instituicao'] = $obj_cod_instituicao_det['nm_instituicao'];

                $linha = [
                    "<a href=\"educar_tipo_ocorrencia_disciplinar_det.php?cod_tipo_ocorrencia_disciplinar={$registro['cod_tipo_ocorrencia_disciplinar']}\">{$registro['nm_tipo']}</a>",
                    "<a href=\"educar_tipo_ocorrencia_disciplinar_det.php?cod_tipo_ocorrencia_disciplinar={$registro['cod_tipo_ocorrencia_disciplinar']}\">{$registro['max_ocorrencias']}</a>",
                ];

                if ($nivel_usuario == 1) {
                    $linha[] = "<a href=\"educar_tipo_ocorrencia_disciplinar_det.php?cod_tipo_ocorrencia_disciplinar={$registro['cod_tipo_ocorrencia_disciplinar']}\">{$registro['ref_cod_instituicao']}</a>";
                }
                $this->addLinhas($linha);
            }
        }

        $this->addPaginador2(
            'educar_tipo_ocorrencia_disciplinar_lst.php',
            $total,
            $_GET,
            $this->nome,
            $this->limite
        );

        if ($obj_permissoes->permissao_cadastra(580, $this->pessoa_logada, 3)) {
            $this->acao = 'go("educar_tipo_ocorrencia_disciplinar_cad.php")';
            $this->nome_acao = __('Novo');
        }

        $this->largura = '100%';

        $this->breadcrumb(__('Listagem de tipos de ocorrências disciplinares'), [
            url('intranet/educar_index.php') => __('Escola'),
        ]);
    }

    public function Formular()
    {
        $this->title = __('Tipo Ocorrência Disciplinar');
        $this->processoAp = '580';
    }
};
