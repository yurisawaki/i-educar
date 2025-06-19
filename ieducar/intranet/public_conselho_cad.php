<?php

use App\Models\Conselho;
use iEducar\Legacy\InteractWithDatabase;

return new class extends clsCadastro {
    use InteractWithDatabase;

    public $cd_conselho;
    public $no_conselho;
    public $ds_conselho;
    public $dt_inicial;
    public $dt_final;
    public $cd_ata;
    public $is_ativo;
    public $cd_nucleo;

    public function model()
    {
        return Conselho::class;
    }

    public function index()
    {
        return 'public_conselho_lst.php';
    }

    public function Inicializar()
    {
        $retorno = 'Novo';

        $this->id = $_GET['id'] ?? null;

        if (is_numeric($this->id)) {
            $conselho = $this->find($this->id);

            $this->no_conselho = $conselho->no_conselho;
            $this->ds_conselho = $conselho->ds_conselho;
            $this->dt_inicial = $conselho->dt_inicial ? date('d/m/Y', strtotime($conselho->dt_inicial)) : null;
            $this->dt_final = $conselho->dt_final ? date('d/m/Y', strtotime($conselho->dt_final)) : null;
            $this->cd_ata = $conselho->cd_ata;
            $this->is_ativo = $conselho->is_ativo;
            $this->cd_nucleo = $conselho->cd_nucleo;

            $retorno = 'Editar';
        }

        $this->url_cancelar = $retorno == 'Editar'
            ? "public_conselho_det.php?id={$this->id}"
            : 'public_conselho_lst.php';

        $nomeMenu = $retorno == 'Editar' ? $retorno : 'Cadastrar';

        $this->breadcrumb("{$nomeMenu} Conselho", [
            url('intranet/educar_conselho_index.php') => 'Conselhos',
        ]);

        return $retorno;
    }

    public function Gerar()
    {
        $this->campoOculto('id', $this->id);

        $this->campoTexto('no_conselho', 'Nome', $this->no_conselho, 50, 100, true);
        $this->campoMemo('ds_conselho', 'Descrição', $this->ds_conselho, 60, 5, false);

        $this->campoData('dt_inicial', 'Data Inicial', $this->dt_inicial, true);
        $this->campoData('dt_final', 'Data Final', $this->dt_final, false);

        $this->campoTexto('cd_ata', 'Código da Ata', $this->cd_ata, 20, 20, false);

        // Campo is_ativo como checkbox
        $this->campoCheck('is_ativo', 'Ativo', $this->is_ativo);

        // Campo cd_nucleo (relacionamento) - pode ser select se quiser
        $this->campoTexto('cd_nucleo', 'Código Núcleo', $this->cd_nucleo, 10, 10, false);
    }

    public function Novo()
    {
        $exists = $this->newQuery()
            ->from('tbl_conselho')
            ->where('no_conselho', request('no_conselho'))
            ->exists();

        if ($exists) {
            $this->mensagem = 'Já existe um Conselho com este nome.<br>';
            return false;
        }

        return $this->create([
            'no_conselho' => request('no_conselho'),
            'ds_conselho' => request('ds_conselho'),
            'dt_inicial' => $this->parseDate(request('dt_inicial')),
            'dt_final' => $this->parseDate(request('dt_final')),
            'cd_ata' => request('cd_ata'),
            'is_ativo' => request('is_ativo') ? 1 : 0,
            'cd_nucleo' => request('cd_nucleo'),
        ]);
    }

    public function Editar()
    {
        $exists = $this->newQuery()
            ->from('tbl_conselho')
            ->where('no_conselho', request('no_conselho'))
            ->where('cd_conselho', '<>', $this->id)
            ->exists();

        if ($exists) {
            $this->mensagem = 'Já existe um Conselho com este nome.<br>';
            return false;
        }

        return $this->update($this->id, [
            'no_conselho' => request('no_conselho'),
            'ds_conselho' => request('ds_conselho'),
            'dt_inicial' => $this->parseDate(request('dt_inicial')),
            'dt_final' => $this->parseDate(request('dt_final')),
            'cd_ata' => request('cd_ata'),
            'is_ativo' => request('is_ativo') ? 1 : 0,
            'cd_nucleo' => request('cd_nucleo'),
        ]);
    }

    public function Excluir()
    {
        return $this->delete($this->id);
    }

    // Função auxiliar para converter datas de dd/mm/yyyy para yyyy-mm-dd
    private function parseDate($date)
    {
        if (!$date) {
            return null;
        }

        $d = \DateTime::createFromFormat('d/m/Y', $date);
        return $d ? $d->format('Y-m-d') : null;
    }

    public function Formular()
    {
        $this->title = 'Conselho';
        $this->processoAp = 760; // ajuste conforme processo correto
    }
};
