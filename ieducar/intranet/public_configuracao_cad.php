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
        $retorno = 'Novo';

        // Sempre busca o primeiro e único registro
        $config = Configuracao::first();

        if ($config) {
            $this->idconfig = $config->id;
            $this->tempo = $config->tempo;
            $this->distancia = $config->distancia;

            $retorno = 'Cadastrar ou Editar';
        }

        $this->url_cancelar = 'public_configuracao_lst.php';
        $this->nome_url_cancelar = 'Cancelar';

        $nomeMenu = $retorno == 'Editar' ? 'Editar' : 'Cadastrar';

        $this->breadcrumb("{$nomeMenu} configuração", [
            url('intranet/educar_configuracao_index.php') => 'Configuração',
        ]);

        return $retorno;
    }

    public function Gerar()
    {
        $this->campoOculto('idconfig', $this->idconfig);
        $this->campoNumero('tempo', 'Tempo (segundos)', $this->tempo, 3, 5, true);
        $this->campoNumero('distancia', 'Distância (metros)', $this->distancia, 3, 5, true);
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
        $this->title = 'Configuração';
        $this->processoAp = 69;  // Ajuste conforme o seu processo
    }
};
