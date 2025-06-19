<?php

use App\Models\Ata;
use iEducar\Legacy\InteractWithDatabase;

return new class extends clsListagem {
    use InteractWithDatabase;

    public $__limite;
    public $__offset;

    public $no_ata;
    public $ds_ata;
    public $ds_votacao;
    public $ds_encerramento;

    public function model()
    {
        return Ata::class;
    }

    public function index()
    {
        return 'public_ata_lst.php';
    }

    public function Gerar()
    {
        $this->__titulo = 'Ata - Listagem';

        // Captura os filtros da query string
        foreach ($_GET as $var => $val) {
            $this->$var = ($val === '') ? null : $val;
        }

        // Cabeçalhos da tabela
        $this->addCabecalhos([
            'Nome da Ata',
            'Descrição',
            'Votação',
            'Encerramento',
            'Criado em',
            'Atualizado em',
            'Deletado em',
            'Ação'
        ]);

        // Campos de filtro
        $this->campoTexto('no_ata', 'Nome da Ata', $this->no_ata, 30, 255, false);
        $this->campoTexto('ds_ata', 'Descrição', $this->ds_ata, 30, 255, false);
        $this->campoTexto('ds_votacao', 'Votação', $this->ds_votacao, 30, 255, false);
        $this->campoTexto('ds_encerramento', 'Encerramento', $this->ds_encerramento, 30, 255, false);

        // Paginação
        $this->__limite = 20;
        $this->__offset = isset($_GET["pagina_{$this->no_ata}"]) ? $_GET["pagina_{$this->no_ata}"] * $this->__limite - $this->__limite : 0;

        [$data, $total] = $this->paginate($this->__limite, $this->__offset, function ($query) {
            $query->when($this->no_ata, function ($query) {
                $query->whereUnaccent('no_ata', $this->no_ata);
            });

            $query->when($this->ds_ata, function ($query) {
                $query->whereUnaccent('ds_ata', $this->ds_ata);
            });

            $query->when($this->ds_votacao, function ($query) {
                $query->whereUnaccent('ds_votacao', $this->ds_votacao);
            });

            $query->when($this->ds_encerramento, function ($query) {
                $query->whereUnaccent('ds_encerramento', $this->ds_encerramento);
            });

            $query->orderBy('no_ata');
        });

        // Adiciona as linhas da tabela
        foreach ($data as $item) {
            $this->addLinhas([
                htmlspecialchars($item->no_ata),
                htmlspecialchars(mb_strimwidth($item->ds_ata, 0, 40, '...')),
                htmlspecialchars(mb_strimwidth($item->ds_votacao, 0, 40, '...')),
                htmlspecialchars(mb_strimwidth($item->ds_encerramento, 0, 40, '...')),
                $item->created_at ? date('d/m/Y H:i', strtotime($item->created_at)) : '',
                $item->updated_at ? date('d/m/Y H:i', strtotime($item->updated_at)) : '',
                $item->deleted_at ? date('d/m/Y H:i', strtotime($item->deleted_at)) : '',
                "<a href=\"public_ata_cad.php?id={$item->cd_ata}\">Editar</a>"
            ]);
        }

        // Paginador
        $this->addPaginador2('public_ata_lst.php', $total, $_GET, $this->no_ata, $this->__limite);

        // Permissões para novo registro
        $obj_permissao = new clsPermissoes;

        if ($obj_permissao->permissao_cadastra(761, $this->pessoa_logada, 7, null, true)) {
            $this->acao = 'go("public_ata_cad.php")';
            $this->nome_acao = 'Novo';
        }

        $this->largura = '100%';

        // Breadcrumb para navegação
        $this->breadcrumb('Listagem de Atas', [
            url('intranet/educar_enderecamento_index.php') => 'Endereçamento',
        ]);
    }

    public function Formular()
    {
        $this->title = 'Ata';
        $this->processoAp = 756; // Código real do processo
    }
};
