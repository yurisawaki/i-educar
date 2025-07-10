<?php

use App\Models\Ata;
use iEducar\Legacy\InteractWithDatabase;

return new class extends clsCadastro {
    use InteractWithDatabase;

    public $cd_ata;
    public $no_ata;
    public $ds_pauta;
    public $ds_votacao;
    public $ds_encerramento;

    public function model()
    {
        return Ata::class;
    }

    public function Inicializar()
    {
        $retorno = __('Novo');

        $this->cd_ata = $_GET['id'] ?? $_POST['cd_ata'] ?? null;

        if ($this->cd_ata) {
            $ata = Ata::find($this->cd_ata);

            if ($ata) {
                $this->no_ata = $ata->no_ata;
                $this->ds_pauta = $ata->ds_pauta;
                $this->ds_votacao = $ata->ds_votacao;
                $this->ds_encerramento = $ata->ds_encerramento;
                $retorno = __('Editar');
            }
        }

        $this->url_cancelar = 'public_ata_lst.php';
        $this->nome_url_cancelar = __('Cancelar');

        $this->breadcrumb(__('Cadastro de Ata'), [
            url('intranet/educar_enderecamento_index.php') => __('Endereçamento'),
            url('public_ata_lst.php') => __('Ata'),
        ]);

        return $retorno;
    }

    public function Gerar()
    {
        $this->campoOculto('cd_ata', $this->cd_ata);
        $this->campoTexto('no_ata', __('Nome da Ata'), $this->no_ata, 50, 255, true);
        $this->campoMemo('ds_pauta', __('Pauta'), $this->ds_pauta, 60, 5, false);
        $this->campoMemo('ds_votacao', __('Votação'), $this->ds_votacao, 60, 5, false);
        $this->campoMemo('ds_encerramento', __('Encerramento'), $this->ds_encerramento, 60, 5, false);
    }

    public function Novo()
    {
        Ata::create([
            'no_ata' => $this->no_ata,
            'ds_pauta' => $this->ds_pauta,
            'ds_votacao' => $this->ds_votacao,
            'ds_encerramento' => $this->ds_encerramento,
        ]);

        $this->mensagem = __('Cadastro realizado com sucesso!');
        return true;
    }

    public function Editar()
    {
        $ata = Ata::findOrFail($this->cd_ata);

        $ata->update([
            'no_ata' => $this->no_ata,
            'ds_pauta' => $this->ds_pauta,
            'ds_votacao' => $this->ds_votacao,
            'ds_encerramento' => $this->ds_encerramento,
        ]);

        $this->mensagem = __('Edição realizada com sucesso!');
        return true;
    }

    public function Excluir()
    {
        $ata = Ata::findOrFail($this->cd_ata);

        $ata->delete();

        $this->mensagem = __('Exclusão realizada com sucesso!');
        return true;
    }
};
