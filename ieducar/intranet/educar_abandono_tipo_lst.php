<?php

return new class extends clsListagem {
    public $pessoa_logada;

    public $titulo;

    public $limite;

    public $offset;

    public $cod_abandono_tipo;

    public $ref_usuario_exc;

    public $ref_usuario_cad;

    public $nome;

    public $data_cadastro;

    public $data_exclusao;

    public $ativo;

    public $ref_cod_instituicao;

    public function Gerar()
    {
        $this->titulo = __('Motivo Abandono') . ' - ' . __('Listagem');

        foreach ($_GET as $var => $val) {
            $this->$var = ($val === '') ? null : $val;
        }

        $lista_busca = [
            __('Abandono'),
        ];

        $obj_permissao = new clsPermissoes;
        $nivel_usuario = $obj_permissao->nivel_acesso(int_idpes_usuario: $this->pessoa_logada);
        if ($nivel_usuario == 1) {
            $lista_busca[] = __('Instituição');
        }

        $this->addCabecalhos(coluna: $lista_busca);

        include 'include/pmieducar/educar_campo_lista.php';

        $this->campoTexto(
            nome: 'nome',
            campo: __('Abandono'),
            valor: $this->nome,
            tamanhovisivel: 30,
            tamanhomaximo: 255,
            obrigatorio: false
        );

        $this->limite = 20;

        $query = \App\Models\LegacyAbandonmentType::query()
            ->where(column: 'ativo', operator: 1)
            ->orderBy(column: 'nome', direction: 'ASC');

        if (is_string(value: $this->nome)) {
            $query->where(column: 'nome', operator: 'ilike', value: '%' . $this->nome . '%');
        }

        if (is_numeric(value: $this->ref_cod_instituicao)) {
            $query->where(column: 'ref_cod_instituicao', operator: $this->ref_cod_instituicao);
        }

        $result = $query->paginate(perPage: $this->limite, pageName: 'pagina_');

        $lista = $result->items();
        $total = $result->total();

        if (is_array(value: $lista) && count(value: $lista)) {
            foreach ($lista as $registro) {
                $obj_cod_instituicao = new clsPmieducarInstituicao(cod_instituicao: $registro['ref_cod_instituicao']);
                $obj_cod_instituicao_det = $obj_cod_instituicao->detalhe();
                $registro['ref_cod_instituicao'] = $obj_cod_instituicao_det['nm_instituicao'];

                $lista_busca = [
                    "<a href=\"educar_abandono_tipo_det.php?cod_abandono_tipo={$registro['cod_abandono_tipo']}\">{$registro['nome']}</a>",
                ];

                if ($nivel_usuario == 1) {
                    $lista_busca[] = "<a href=\"educar_abandono_tipo_det.php?cod_abandono_tipo={$registro['cod_abandono_tipo']}\">{$registro['ref_cod_instituicao']}</a>";
                }
                $this->addLinhas(linha: $lista_busca);
            }
        }

        $this->addPaginador2(
            strUrl: 'educar_abandono_tipo_lst.php',
            intTotalRegistros: $total,
            mixVariaveisMantidas: $_GET,
            nome: null,
            intResultadosPorPagina: $this->limite
        );

        if ($obj_permissao->permissao_cadastra(950, $this->pessoa_logada, 7)) {
            $this->acao = 'go("educar_abandono_tipo_cad.php")';
            $this->nome_acao = __('Novo');
        }

        $this->largura = '100%';

        $this->breadcrumb(
            currentPage: __('Listagem de tipos de abandono'),
            breadcrumbs: [
                url(path: 'intranet/educar_index.php') => __('Escola'),
            ]
        );
    }

    public function Formular()
    {
        $this->title = __('Motivo Abandono');
        $this->processoAp = '950';
    }
};
