<?php

use App\Models\Reuniao;
use iEducar\Legacy\InteractWithDatabase;

return new class extends clsListagem {
    use InteractWithDatabase;

    public $__limite;
    public $__offset;

    public $no_reuniao;
    public $ds_reuniao;
    public $cd_reuniao_status;

    public function model()
    {
        return Reuniao::class;
    }

    public function index()
    {
        return 'public_reuniao_lst.php';
    }

    public function Gerar()
    {
        $this->__titulo = 'Reunião - Listagem';

        // Captura os filtros da query string
        foreach ($_GET as $var => $val) {
            $this->$var = ($val === '') ? null : $val;
        }

        // Cabeçalhos da tabela
        $this->addCabecalhos([
            'Nome da Reunião',
            'Descrição',
            'Status',
            'Data da Reunião',
            'Ação'
        ]);

        // Campos de filtro
        $this->campoTexto('no_reuniao', 'Nome da Reunião', $this->no_reuniao, 30, 255, false);
        $this->campoTexto('ds_reuniao', 'Descrição', $this->ds_reuniao, 30, 255, false);

        // Paginação
        $this->__limite = 20;
        $this->__offset = isset($_GET["pagina_{$this->no_reuniao}"]) ? $_GET["pagina_{$this->no_reuniao}"] * $this->__limite - $this->__limite : 0;

        [$data, $total] = $this->paginate($this->__limite, $this->__offset, function ($query) {
            $query->when($this->no_reuniao, function ($query) {
                $query->whereUnaccent('no_reuniao', $this->no_reuniao);
            });

            $query->when($this->ds_reuniao, function ($query) {
                $query->whereUnaccent('ds_reuniao', $this->ds_reuniao);
            });

            if ($this->cd_reuniao_status !== null && $this->cd_reuniao_status !== '') {
                $query->where('cd_reuniao_status', $this->cd_reuniao_status);
            }

            $query->orderBy('no_reuniao');
        });

        // Adiciona as linhas da tabela
        foreach ($data as $item) {
            $this->addLinhas([
                htmlspecialchars($item->no_reuniao),
                htmlspecialchars(mb_strimwidth($item->ds_reuniao, 0, 40, '...')),
                $item->cd_reuniao_status, // Aqui pode ser traduzido para texto, se desejar
                $item->dh_reuniao ? date('d/m/Y H:i', strtotime($item->dh_reuniao)) : '',
                "<a href=\"public_reuniao_cad.php?id={$item->cd_reuniao}\">Editar</a>"
            ]);
        }

        // Paginador
        $this->addPaginador2('public_reuniao_lst.php', $total, $_GET, $this->no_reuniao, $this->__limite);

        // Permissões para novo registro
        $obj_permissao = new clsPermissoes;

        if ($obj_permissao->permissao_cadastra(760, $this->pessoa_logada, 7, null, true)) {
            $this->acao = 'go("public_reuniao_cad.php")';
            $this->nome_acao = 'Novo';
        }

        $this->largura = '100%';

        // Breadcrumb para navegação
        $this->breadcrumb('Listagem de Reuniões', [
            url('intranet/educar_enderecamento_index.php') => 'Endereçamento',
        ]);
    }

    public function Formular()
    {
        $this->title = 'Reunião';
        $this->processoAp = 761; // Código real do processo (altere conforme necessário)
    }
};
