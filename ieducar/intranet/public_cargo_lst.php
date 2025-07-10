<?php

use App\Models\Cargo;
use iEducar\Legacy\InteractWithDatabase;

return new class extends clsListagem {
    use InteractWithDatabase;

    public $__limite;
    public $__offset;

    public $ds_cargo;

    public function model()
    {
        return Cargo::class;
    }

    public function index()
    {
        return 'public_cargo_lst.php';
    }

    public function Gerar()
    {
        $this->__titulo = __('Cargo - Listagem');

        foreach ($_GET as $var => $val) {
            $this->$var = ($val === '') ? null : $val;
        }

        $this->addCabecalhos([
            __('Descrição do Cargo'),
            __('Ação')
        ]);

        $this->campoTexto('ds_cargo', __('Descrição do Cargo'), $this->ds_cargo, 30, 255, false);

        $this->__limite = 20;
        $this->__offset = isset($_GET["pagina_{$this->ds_cargo}"]) ? $_GET["pagina_{$this->ds_cargo}"] * $this->__limite - $this->__limite : 0;

        [$data, $total] = $this->paginate($this->__limite, $this->__offset, function ($query) {
            $query->when($this->ds_cargo, function ($query) {
                $query->whereUnaccent('ds_cargo', $this->ds_cargo);
            });

            $query->orderBy('ds_cargo');
        });

        foreach ($data as $item) {
            $this->addLinhas([
                htmlspecialchars($item->ds_cargo),
                "<a href=\"public_cargo_cad.php?id={$item->cd_cargo}\">" . __('Editar') . "</a>"
            ]);
        }

        $this->addPaginador2('public_cargo_lst.php', $total, $_GET, $this->ds_cargo, $this->__limite);

        $obj_permissao = new clsPermissoes;

        if ($obj_permissao->permissao_cadastra(758, $this->pessoa_logada, 7, null, true)) {
            $this->acao = 'go("public_cargo_cad.php")';
            $this->nome_acao = __('Novo');
        }

        $this->largura = '100%';

        $this->breadcrumb(__('Listagem de Cargos'), [
            url('intranet/educar_enderecamento_index.php') => __('Endereçamento'),
        ]);
    }

    public function Formular()
    {
        $this->title = __('Cargo');
        $this->processoAp = 757;
    }
};
