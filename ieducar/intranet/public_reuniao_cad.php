<?php

use App\Models\Reuniao;
use iEducar\Legacy\InteractWithDatabase;

return new class extends clsCadastro {
    use InteractWithDatabase;

    public $cd_reuniao;
    public $cd_conselho;
    public $ds_reuniao;
    public $cd_reuniao_status;
    public $cd_local;
    public $dh_reuniao;
    public $dh_primeira_chamada;
    public $dh_segunda_chamada;
    public $dh_inicial;
    public $dh_final;
    public $cd_convocatoria;
    public $dh_convocatoria;
    public $cd_ata;
    public $no_reuniao;

    public function model()
    {
        return Reuniao::class;
    }

    public function index()
    {
        return 'public_reuniao_lst.php';
    }

    public function Inicializar()
    {
        $retorno = __('Novo');

        $this->cd_reuniao = $_GET['cd_reuniao'] ?? null;

        if (is_numeric($this->cd_reuniao)) {
            $reuniao = $this->find($this->cd_reuniao);

            $this->cd_conselho = $reuniao->cd_conselho;
            $this->ds_reuniao = $reuniao->ds_reuniao;
            $this->cd_reuniao_status = $reuniao->cd_reuniao_status;
            $this->cd_local = $reuniao->cd_local;
            $this->dh_reuniao = $reuniao->dh_reuniao;
            $this->dh_primeira_chamada = $reuniao->dh_primeira_chamada;
            $this->dh_segunda_chamada = $reuniao->dh_segunda_chamada;
            $this->dh_inicial = $reuniao->dh_inicial;
            $this->dh_final = $reuniao->dh_final;
            $this->cd_convocatoria = $reuniao->cd_convocatoria;
            $this->dh_convocatoria = $reuniao->dh_convocatoria;
            $this->cd_ata = $reuniao->cd_ata;
            $this->no_reuniao = $reuniao->no_reuniao;

            $retorno = __('Editar');
        }

        $this->url_cancelar = $retorno == __('Editar')
            ? "public_reuniao_det.php?cd_reuniao={$this->cd_reuniao}"
            : 'public_reuniao_lst.php';

        $this->nome_url_cancelar = __('Cancelar');

        $nomeMenu = $retorno == __('Editar') ? $retorno : __('Cadastrar');

        $this->breadcrumb("{$nomeMenu} " . __('Reunião'), [
            url('intranet/educar_reuniao_index.php') => __('Reuniões'),
        ]);

        return $retorno;
    }

    public function Gerar()
    {
        $this->campoOculto('cd_reuniao', $this->cd_reuniao);

        $this->campoTexto('no_reuniao', __('Nome da Reunião'), $this->no_reuniao, 50, 100, true);
        $this->campoTexto('ds_reuniao', __('Descrição'), $this->ds_reuniao, 60, 255, false);

        $this->campoTexto('cd_conselho', __('Código do Conselho'), $this->cd_conselho, 10, 10, true);
        $this->campoTexto('cd_reuniao_status', __('Status da Reunião'), $this->cd_reuniao_status, 10, 10, true);
        $this->campoTexto('cd_local', __('Código do Local'), $this->cd_local, 10, 10, false);

        $this->campoTexto('dh_reuniao', __('Data e Hora da Reunião (YYYY-MM-DD HH:MM)'), $this->dh_reuniao, 16, 16, true);
        $this->campoTexto('dh_primeira_chamada', __('Data e Hora Primeira Chamada (YYYY-MM-DD HH:MM)'), $this->dh_primeira_chamada, 16, 16, false);
        $this->campoTexto('dh_segunda_chamada', __('Data e Hora Segunda Chamada (YYYY-MM-DD HH:MM)'), $this->dh_segunda_chamada, 16, 16, false);

        $this->campoData('dh_inicial', __('Data e Hora Início (YYYY-MM-DD HH:MM)'), $this->dh_inicial, 16, 16, true);
        $this->campoData('dh_final', __('Data e Hora Final (YYYY-MM-DD HH:MM)'), $this->dh_final, 16, 16, true);

        $this->campoTexto('cd_convocatoria', __('Código da Convocatória'), $this->cd_convocatoria, 10, 10, false);
        $this->campoTexto('dh_convocatoria', __('Data e Hora da Convocatória (YYYY-MM-DD HH:MM)'), $this->dh_convocatoria, 16, 16, false);
        $this->campoTexto('cd_ata', __('Código da Ata'), $this->cd_ata, 10, 10, false);
    }

    public function Novo()
    {
        $exists = $this->newQuery()
            ->from('tbl_reuniao')
            ->where('no_reuniao', request('no_reuniao'))
            ->exists();

        if ($exists) {
            $this->mensagem = __('Já existe uma Reunião com este nome.') . '<br>';
            return false;
        }

        return $this->create([
            'cd_conselho' => request('cd_conselho'),
            'ds_reuniao' => request('ds_reuniao'),
            'cd_reuniao_status' => request('cd_reuniao_status'),
            'cd_local' => request('cd_local'),
            'dh_reuniao' => request('dh_reuniao'),
            'dh_primeira_chamada' => request('dh_primeira_chamada'),
            'dh_segunda_chamada' => request('dh_segunda_chamada'),
            'dh_inicial' => request('dh_inicial'),
            'dh_final' => request('dh_final'),
            'cd_convocatoria' => request('cd_convocatoria'),
            'dh_convocatoria' => request('dh_convocatoria'),
            'cd_ata' => request('cd_ata'),
            'no_reuniao' => request('no_reuniao'),
        ]);
    }

    public function Editar()
    {
        $exists = $this->newQuery()
            ->from('tbl_reuniao')
            ->where('no_reuniao', request('no_reuniao'))
            ->where('cd_reuniao', '<>', $this->cd_reuniao)
            ->exists();

        if ($exists) {
            $this->mensagem = __('Já existe uma Reunião com este nome.') . '<br>';
            return false;
        }

        return $this->update($this->cd_reuniao, [
            'cd_conselho' => request('cd_conselho'),
            'ds_reuniao' => request('ds_reuniao'),
            'cd_reuniao_status' => request('cd_reuniao_status'),
            'cd_local' => request('cd_local'),
            'dh_reuniao' => request('dh_reuniao'),
            'dh_primeira_chamada' => request('dh_primeira_chamada'),
            'dh_segunda_chamada' => request('dh_segunda_chamada'),
            'dh_inicial' => request('dh_inicial'),
            'dh_final' => request('dh_final'),
            'cd_convocatoria' => request('cd_convocatoria'),
            'dh_convocatoria' => request('dh_convocatoria'),
            'cd_ata' => request('cd_ata'),
            'no_reuniao' => request('no_reuniao'),
        ]);
    }

    public function Excluir()
    {
        return $this->delete($this->cd_reuniao);
    }

    public function Formular()
    {
        $this->title = __('Reunião');
        $this->processoAp = 761;
    }
};
