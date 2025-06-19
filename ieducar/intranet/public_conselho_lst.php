<?php

use App\Models\Conselho;
use iEducar\Legacy\InteractWithDatabase;

return new class extends clsListagem {
    use InteractWithDatabase;

    public $__limite;
    public $__offset;

    public $no_conselho;
    public $ds_conselho;
    public $is_ativo;

    public function model()
    {
        return Conselho::class;
    }

    public function index()
    {
        return 'public_conselho_lst.php';
    }

    public function Gerar()
    {
        $this->__titulo = 'Conselho - Listagem';

        // Captura os filtros da query string
        foreach ($_GET as $var => $val) {
            $this->$var = ($val === '') ? null : $val;
        }

        // Cabeçalhos da tabela
        $this->addCabecalhos([
            'Nome do Conselho',
            'Descrição',
            'Data Inicial',
            'Data Final',
            'Ativo',
            'Ação'
        ]);

        // Campos de filtro
        $this->campoTexto('no_conselho', 'Nome do Conselho', $this->no_conselho, 30, 255, false);
        $this->campoTexto('ds_conselho', 'Descrição', $this->ds_conselho, 30, 255, false);


        // Paginação
        $this->__limite = 20;
        $this->__offset = isset($_GET["pagina_{$this->no_conselho}"]) ? $_GET["pagina_{$this->no_conselho}"] * $this->__limite - $this->__limite : 0;

        [$data, $total] = $this->paginate($this->__limite, $this->__offset, function ($query) {
            $query->when($this->no_conselho, function ($query) {
                $query->whereUnaccent('no_conselho', $this->no_conselho);
            });

            $query->when($this->ds_conselho, function ($query) {
                $query->whereUnaccent('ds_conselho', $this->ds_conselho);
            });

            if ($this->is_ativo !== null && $this->is_ativo !== '') {
                $query->where('is_ativo', $this->is_ativo);
            }

            $query->orderBy('no_conselho');
        });

        // Adiciona as linhas da tabela
        foreach ($data as $item) {
            $this->addLinhas([
                htmlspecialchars($item->no_conselho),
                htmlspecialchars(mb_strimwidth($item->ds_conselho, 0, 40, '...')),
                $item->dt_inicial ? date('d/m/Y', strtotime($item->dt_inicial)) : '',
                $item->dt_final ? date('d/m/Y', strtotime($item->dt_final)) : '',
                $item->is_ativo ? 'Sim' : 'Não',
                "<a href=\"public_conselho_cad.php?id={$item->cd_conselho}\">Editar</a>"
            ]);
        }

        // Paginador
        $this->addPaginador2('public_conselho_lst.php', $total, $_GET, $this->no_conselho, $this->__limite);

        // Permissões para novo registro
        $obj_permissao = new clsPermissoes;

        if ($obj_permissao->permissao_cadastra(759, $this->pessoa_logada, 7, null, true)) {
            $this->acao = 'go("public_conselho_cad.php")';
            $this->nome_acao = 'Novo';
        }

        $this->largura = '100%';

        // Breadcrumb para navegação
        $this->breadcrumb('Listagem de Conselhos', [
            url('intranet/educar_enderecamento_index.php') => 'Endereçamento',
        ]);
    }

    public function Formular()
    {
        $this->title = 'Conselho';
        $this->processoAp = 760; // Código real do processo
    }
};
