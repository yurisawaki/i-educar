<?php

use App\Models\Configuracao;
use iEducar\Legacy\InteractWithDatabase;

return new class extends clsCadastro {
    use InteractWithDatabase;

    public $idconfig;
    public $tempo;
    public $distancia;

    public function model()
    {
        return Configuracao::class;
    }

    public function index()
    {
        return 'public_configuracao_lst.php';
    }
    public function Inicializar()
    {
        $config = Configuracao::first();

        if ($config) {
            $this->idconfig = $config->id;
            $this->tempo = $config->tempo;
            $this->distancia = $config->distancia;
        }

        $this->url_cancelar = 'public_configuracao_lst.php';
        $this->nome_url_cancelar = 'Cancelar';

        $this->breadcrumb('Configuração', [
            url('intranet/educar_configuracao_index.php') => 'Configuração',
        ]);

        return 'Novo'; // força sempre cair em Novo()
    }

    public function Novo()
    {
        $data = [
            'tempo' => request()->input('tempo'),
            'distancia' => request()->input('distancia'),
        ];

        $config = Configuracao::first();

        if ($config) {
            return $config->update($data);
        }

        return Configuracao::create($data) ? true : false;
    }


    public function Gerar()
    {
        $this->campoOculto('idconfig', $this->idconfig);
        $this->campoNumero('tempo', 'Tempo (segundos)', $this->tempo, 3, 5, true);
        $this->campoNumero('distancia', 'Distância (metros)', $this->distancia, 3, 5, true);
    }



    public function Editar()
    {
        // Sempre atualiza o existente
        return $this->update($this->idconfig, [
            'tempo' => request()->input('tempo'),
            'distancia' => request()->input('distancia'),
        ]);
    }

    public function Excluir()
    {
        return $this->delete($this->idconfig);
    }

    public function makeExtra()
    {
        return file_get_contents(__DIR__ . '/scripts/extra/public-configuracao-cad.js');
    }

    public function Formular()
    {
        $this->title = 'Configuração';
        $this->processoAp = 313131;
    }
};
