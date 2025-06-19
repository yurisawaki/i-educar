<?php

use App\Models\Configuracao;
use iEducar\Legacy\InteractWithDatabase;

return new class extends clsListagem {
    use InteractWithDatabase;

    public $__limite;
    public $__offset;

    public $tempo;
    public $distancia;

    public function model()
    {
        return Configuracao::class;
    }

    public function Gerar()
    {
        $this->__titulo = 'Configuração - Listagem';

        foreach ($_GET as $var => $val) {
            $this->$var = ($val === '') ? null : $val;
        }

        $this->addCabecalhos([
            'Tempo (s)',
            'Distância (m)',
            'Ações'
        ]);

        $this->campoNumero('tempo', 'Tempo (s)', $this->tempo, 3, 5, false);
        $this->campoNumero('distancia', 'Distância (m)', $this->distancia, 3, 5, false);

        $this->__limite = 20;
        $this->__offset = ($_GET["pagina_{$this->nome}"]) ? $_GET["pagina_{$this->nome}"] * $this->__limite - $this->__limite : 0;

        [$data, $total] = $this->paginate($this->__limite, $this->__offset, function ($query) {
            $query->orderBy('id');
            $query->when($this->tempo, function ($query) {
                $query->where('tempo', $this->tempo);
            });
            $query->when($this->distancia, function ($query) {
                $query->where('distancia', $this->distancia);
            });
        });

        foreach ($data as $config) {
            $this->addLinhas([
                "<a href=\"public_configuracao_cad.php?idconfig={$config->id}\">{$config->tempo}</a>",
                "<a href=\"public_configuracao_cad.php?idconfig={$config->id}\">{$config->distancia}</a>",
                "<a href=\"public_configuracao_cad.php?idconfig={$config->id}\">Editar</a>"
            ]);
        }

        $this->addPaginador2('public_configuracao_lst.php', $total, $_GET, $this->nome, $this->__limite);

        $this->largura = '100%';

        $obj_permissao = new clsPermissoes;

        if ($obj_permissao->permissao_cadastra(999, $this->pessoa_logada, 7, null, true)) {
            $this->acao = 'go("public_configuracao_cad.php")';
            $this->nome_acao = 'Novo';
        }

        $this->breadcrumb('Listagem de configurações', [
            url('intranet/educar_configuracao_index.php') => 'Configuração',
        ]);
    }

    public function makeExtra()
    {
        return file_get_contents(__DIR__ . '/scripts/extra/public-configuracao-lst.js');
    }

    public function Formular()
    {
        $this->title = 'Configuração';
        $this->processoAp = 69;
    }
};
