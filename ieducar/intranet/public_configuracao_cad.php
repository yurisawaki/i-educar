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
        $retorno = __('Novo');


        $config = Configuracao::first();

        if ($config) {
            $this->idconfig = $config->id;
            $this->tempo = $config->tempo;
            $this->distancia = $config->distancia;

            $retorno = __('Cadastrar ou Editar');
        }

        $this->url_cancelar = 'public_configuracao_lst.php';
        $this->nome_url_cancelar = __('Cancelar');

        $nomeMenu = $retorno == __('Editar') ? __('Editar') : __('Cadastrar');

        $this->breadcrumb("{$nomeMenu} " . __('configuração'), [
            url('intranet/educar_configuracao_index.php') => __('Configuração'),
        ]);

        return $retorno;
    }

    public function Gerar()
    {
        $this->campoOculto('idconfig', $this->idconfig);
        $this->campoNumero('tempo', __('Tempo (segundos)'), $this->tempo, 3, 5, true);
        $this->campoNumero('distancia', __('Distância (metros)'), $this->distancia, 3, 5, true);
    }

    public function Novo()
    {
        $config = Configuracao::first();

        if ($config) {
            // Se já existe, atualiza
            return $this->update($config->id, [
                'tempo' => request('tempo'),
                'distancia' => request('distancia'),
            ]);
        }

        // Se não existe, cria
        return $this->create([
            'tempo' => request('tempo'),
            'distancia' => request('distancia'),
        ]);
    }

    public function Editar()
    {
        // Sempre atualiza o existente
        return $this->update($this->idconfig, [
            'tempo' => request('tempo'),
            'distancia' => request('distancia'),
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
        $this->title = __('Configuração');
        $this->processoAp = 69;
    }
};
