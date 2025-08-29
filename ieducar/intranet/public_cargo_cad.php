<?php

use App\Models\Cargo;
use iEducar\Legacy\InteractWithDatabase;

return new class extends clsCadastro {
    use InteractWithDatabase;

    public $cd_cargo;
    public $ds_cargo;

    public function model()
    {
        return Cargo::class;
    }

    public function index()
    {
        return 'public_cargo_lst.php';
    }

    public function Inicializar()
    {
        $retorno = __('Novo');
        $this->cd_cargo = $_GET['cd_cargo'] ?? null;

        if (is_numeric($this->cd_cargo)) {
            $cargo = $this->find($this->cd_cargo);
            $this->ds_cargo = $cargo->ds_cargo;
            $retorno = __('Editar');
        }

        $this->url_cancelar = $retorno == __('Editar')
            ? 'public_cargo_det.php?cd_cargo=' . $this->cd_cargo
            : 'public_cargo_lst.php';

        $this->nome_url_cancelar = __('Cancelar');

        $this->breadcrumb("{$retorno} " . __('Cargo'), [
            url('intranet/educar_enderecamento_index.php') => __('Endereçamento')
        ]);

        return $retorno;
    }

    public function Gerar()
    {
        $this->campoOculto('cd_cargo', $this->cd_cargo);
        $this->campoTexto('ds_cargo', __('Descrição do Cargo'), $this->ds_cargo, 30, 255, true);
    }

    public function Novo()
    {
        return $this->create(['ds_cargo' => request('ds_cargo')]);
    }

    public function Editar()
    {
        return $this->update($this->cd_cargo, ['ds_cargo' => request('ds_cargo')]);
    }

    public function Excluir()
    {
        return $this->delete($this->cd_cargo);
    }

    public function Formular()
    {
        $this->title = __('Cargo');
        $this->processoAp = 757;
    }
};
