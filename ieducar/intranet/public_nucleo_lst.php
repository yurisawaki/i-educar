<?php

use App\Models\Nucleo;
use iEducar\Legacy\InteractWithDatabase;

return new class extends clsListagem {
    use InteractWithDatabase;

    public $__limite;
    public $__offset;

    public $nm_nucleo;
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
        $this->__titulo = __('Núcleo - Listagem');

        foreach ($_GET as $var => $val) {
            $this->$var = ($val === '') ? null : $val;
        }

        $this->addCabecalhos([
            __('Nome do Núcleo'),
            __('Descrição'),
            __('Ação')
        ]);

        $this->campoTexto('nm_nucleo', __('Nome do Núcleo'), $this->nm_nucleo, 30, 255, false);
        $this->campoTexto('ds_nucleo', __('Descrição'), $this->ds_nucleo, 30, 255, false);

        $this->__limite = 20;
        $this->__offset = isset($_GET["pagina_{$this->nm_nucleo}"]) ? $_GET["pagina_{$this->nm_nucleo}"] * $this->__limite - $this->__limite : 0;

        [$data, $total] = $this->paginate($this->__limite, $this->__offset, function ($query) {
            $query->when($this->nm_nucleo, function ($query) {
                $query->whereUnaccent('no_nucleo', $this->nm_nucleo);
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
                "<a href=\"public_nucleo_cad.php?id={$item->cd_nucleo}\">" . __('Editar') . "</a>"
            ]);
        }

        $this->addPaginador2('public_nucleo_lst.php', $total, $_GET, $this->nm_nucleo, $this->__limite);

        $obj_permissao = new clsPermissoes;

        if ($obj_permissao->permissao_cadastra(758, $this->pessoa_logada, 7, null, true)) {
            $this->acao = 'go("public_nucleo_cad.php")';
            $this->nome_acao = __('Novo');
        }

        $this->largura = '100%';

        $this->breadcrumb(__('Listagem de Núcleos'), [
            url('intranet/educar_enderecamento_index.php') => __('Endereçamento'),
        ]);
    }

    public function Formular()
    {
        $this->title = __('Núcleo');
        $this->processoAp = 760;
    }
};
