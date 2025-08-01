<?php

use App\Models\Nucleo;
use iEducar\Legacy\InteractWithDatabase;

return new class extends clsListagem {
    use InteractWithDatabase;

    public $__limite;
    public $__offset;

    public $no_nucleo;
    public $ds_nucleo;

    public function model()
    {
        return Nucleo::class;
    }

    public function index()
    {
        return 'public_nucleo_lst.php';
    }

    public function Gerar()
    {
        $this->__titulo = 'Núcleo - Listagem';

        foreach ($_GET as $var => $val) {
            $this->$var = ($val === '') ? null : $val;
        }

        $this->addCabecalhos([
            'Nome do Núcleo',
            'Descrição',
            'Ação'
        ]);

        $this->campoTexto('no_nucleo', 'Nome do Núcleo', $this->no_nucleo, 30, 255, false);
        $this->campoTexto('ds_nucleo', 'Descrição', $this->ds_nucleo, 30, 255, false);

        $this->__limite = 20;
        $this->__offset = isset($_GET["pagina_{$this->no_nucleo}"]) ? $_GET["pagina_{$this->no_nucleo}"] * $this->__limite - $this->__limite : 0;

        [$data, $total] = $this->paginate($this->__limite, $this->__offset, function ($query) {
            $query->when($this->no_nucleo, function ($query) {
                $query->whereUnaccent('no_nucleo', $this->no_nucleo);
            });

            $query->when($this->ds_nucleo, function ($query) {
                $query->whereUnaccent('ds_nucleo', $this->ds_nucleo);
            });

            $query->orderBy('no_nucleo');
        });

        foreach ($data as $item) {
            $this->addLinhas([
                htmlspecialchars($item->no_nucleo),
                htmlspecialchars($item->ds_nucleo),
                "<a href=\"public_nucleo_cad.php?id={$item->cd_nucleo}\">Editar</a>"
            ]);
        }

        $this->addPaginador2('public_nucleo_lst.php', $total, $_GET, $this->no_nucleo, $this->__limite);

        $obj_permissao = new clsPermissoes;

        if ($obj_permissao->permissao_cadastra(758, $this->pessoa_logada, 7, null, true)) {
            $this->acao = 'go("public_nucleo_cad.php")';
            $this->nome_acao = 'Novo';
        }

        $this->largura = '100%';

        $this->breadcrumb('Listagem de Núcleos', [
            url('intranet/educar_enderecamento_index.php') => 'Endereçamento',
        ]);
    }

    public function Formular()
    {
        $this->title = 'Núcleo';
        $this->processoAp = 758; // Código real do processo
    }
};
