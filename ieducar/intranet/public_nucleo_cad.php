<?php

use App\Models\Nucleo;
use iEducar\Legacy\InteractWithDatabase;

return new class extends clsCadastro {
    use InteractWithDatabase;

    public $cd_nucleo;

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

    public function Inicializar()
    {
        $retorno = 'Novo';

        $this->cd_nucleo = $_GET['cd_nucleo'] ?? null;

        if (is_numeric($this->cd_nucleo)) {
            $nucleo = $this->find($this->cd_nucleo);

            $this->no_nucleo = $nucleo->no_nucleo;
            $this->ds_nucleo = $nucleo->ds_nucleo;

            $retorno = 'Editar';
        }

        $this->url_cancelar = $retorno == 'Editar'
            ? "public_nucleo_det.php?cd_nucleo={$this->cd_nucleo}"
            : 'public_nucleo_lst.php';

        $this->nome_url_cancelar = 'Cancelar';

        $nomeMenu = $retorno == 'Editar' ? $retorno : 'Cadastrar';

        $this->breadcrumb("{$nomeMenu} Núcleo", [
            url('intranet/educar_nucleo_index.php') => 'Núcleos',
        ]);

        return $retorno;
    }

    public function Gerar()
    {
        $this->campoOculto('cd_nucleo', $this->cd_nucleo);

        $this->campoTexto('no_nucleo', 'Nome', $this->no_nucleo, 50, 100, true);

        $this->campoMemo('ds_nucleo', 'Descrição', $this->ds_nucleo, 60, 5, false);
    }

    public function Novo()
    {
        $exists = $this->newQuery()
            ->from('tbl_nucleo')
            ->where('no_nucleo', request('no_nucleo'))
            ->exists();

        if ($exists) {
            $this->mensagem = 'Já existe um Núcleo com este nome.<br>';
            return false;
        }

        return $this->create([
            'no_nucleo' => request('no_nucleo'),
            'ds_nucleo' => request('ds_nucleo'),
        ]);
    }

    public function Editar()
    {
        $exists = $this->newQuery()
            ->from('tbl_nucleo')
            ->where('no_nucleo', request('no_nucleo'))
            ->where('cd_nucleo', '<>', $this->cd_nucleo)
            ->exists();

        if ($exists) {
            $this->mensagem = 'Já existe um Núcleo com este nome.<br>';
            return false;
        }

        return $this->update($this->cd_nucleo, [
            'no_nucleo' => request('no_nucleo'),
            'ds_nucleo' => request('ds_nucleo'),
        ]);
    }

    public function Excluir()
    {
        return $this->delete($this->cd_nucleo);
    }

    public function Formular()
    {
        $this->title = 'Núcleo';
        $this->processoAp = 758; // ajuste conforme o processo correto
    }
};
