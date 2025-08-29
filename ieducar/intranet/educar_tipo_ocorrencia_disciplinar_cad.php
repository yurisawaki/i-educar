<?php

use App\Models\LegacyDisciplinaryOccurrenceType;

return new class extends clsCadastro {
    public $pessoa_logada;

    public $cod_tipo_ocorrencia_disciplinar;

    public $ref_usuario_exc;

    public $ref_usuario_cad;

    public $nm_tipo;

    public $descricao;

    public $max_ocorrencias;

    public $data_cadastro;

    public $data_exclusao;

    public $ativo;

    public $ref_cod_instituicao;

    public function Inicializar()
    {
        $retorno = __('Novo');

        $this->cod_tipo_ocorrencia_disciplinar = $_GET['cod_tipo_ocorrencia_disciplinar'];

        $obj_permissoes = new clsPermissoes;
        $obj_permissoes->permissao_cadastra(580, $this->pessoa_logada, 3, 'educar_tipo_ocorrencia_disciplinar_lst.php');

        if (is_numeric($this->cod_tipo_ocorrencia_disciplinar)) {
            $registro = LegacyDisciplinaryOccurrenceType::find($this->cod_tipo_ocorrencia_disciplinar)?->getAttributes();
            if ($registro) {
                foreach ($registro as $campo => $val) {
                    $this->$campo = $val;
                }

                $this->fexcluir = $obj_permissoes->permissao_excluir(580, $this->pessoa_logada, 3);
                $retorno = __('Editar');
            }
        }

        $this->url_cancelar = ($retorno == __('Editar')) ?
            "educar_tipo_ocorrencia_disciplinar_det.php?cod_tipo_ocorrencia_disciplinar={$registro['cod_tipo_ocorrencia_disciplinar']}" :
            'educar_tipo_ocorrencia_disciplinar_lst.php';

        $nomeMenu = $retorno == __('Editar') ? $retorno : __('Cadastrar');

        $this->breadcrumb(__($nomeMenu . ' tipo de ocorrência disciplinar'), [
            url('intranet/educar_index.php') => __('Escola'),
        ]);

        $this->nome_url_cancelar = __('Cancelar');

        return $retorno;
    }

    public function Gerar()
    {
        $this->campoOculto('cod_tipo_ocorrencia_disciplinar', $this->cod_tipo_ocorrencia_disciplinar);

        $obrigatorio = true;
        include 'include/pmieducar/educar_campo_lista.php';

        $this->campoTexto(
            'nm_tipo',
            __('Tipo Ocorrência Disciplinar'),
            $this->nm_tipo,
            30,
            255,
            true
        );

        $this->campoMemo(
            'descricao',
            __('Descrição'),
            $this->descricao,
            60,
            5,
            false
        );

        $this->campoNumero(
            'max_ocorrencias',
            __('Máximo Ocorrências'),
            $this->max_ocorrencias,
            4,
            4,
            false
        );
    }

    public function Novo()
    {
        $ocorrencia = new LegacyDisciplinaryOccurrenceType;
        $ocorrencia->ref_usuario_cad = $this->pessoa_logada;
        $ocorrencia->nm_tipo = $this->nm_tipo;
        $ocorrencia->descricao = $this->descricao;
        $ocorrencia->max_ocorrencias = is_numeric($this->max_ocorrencias) ? $this->max_ocorrencias : null;
        $ocorrencia->ref_cod_instituicao = $this->ref_cod_instituicao;

        if ($ocorrencia->save()) {
            $this->mensagem .= __('Cadastro efetuado com sucesso.') . '<br>';
            $this->simpleRedirect('educar_tipo_ocorrencia_disciplinar_lst.php');
        }

        $this->mensagem = __('Cadastro não realizado.') . '<br>';

        return false;
    }

    public function Editar()
    {
        $ocorrencia = LegacyDisciplinaryOccurrenceType::find($this->cod_tipo_ocorrencia_disciplinar);
        $ocorrencia->ref_usuario_exc = $this->pessoa_logada;
        $ocorrencia->nm_tipo = $this->nm_tipo;
        $ocorrencia->descricao = $this->descricao;
        $ocorrencia->max_ocorrencias = is_numeric($this->max_ocorrencias) ? $this->max_ocorrencias : null;
        $ocorrencia->ref_cod_instituicao = $this->ref_cod_instituicao;
        $ocorrencia->ativo = 1;

        if ($ocorrencia->save()) {
            $this->mensagem .= __('Edição efetuada com sucesso.') . '<br>';
            $this->simpleRedirect('educar_tipo_ocorrencia_disciplinar_lst.php');
        }

        $this->mensagem = __('Edição não realizada.') . '<br>';

        return false;
    }

    public function Excluir()
    {
        $ocorrencia = LegacyDisciplinaryOccurrenceType::find($this->cod_tipo_ocorrencia_disciplinar);
        $ocorrencia->ativo = 0;
        $ocorrencia->ref_usuario_exc = $this->pessoa_logada;

        if ($ocorrencia->save()) {
            $this->mensagem .= __('Exclusão efetuada com sucesso.') . '<br>';
            $this->simpleRedirect('educar_tipo_ocorrencia_disciplinar_lst.php');
        }

        $this->mensagem = __('Exclusão não realizada.') . '<br>';

        return false;
    }

    public function Formular()
    {
        $this->title = __('Tipo Ocorrência Disciplinar');
        $this->processoAp = '580';
    }
};
