<?php

return new class extends clsDetalhe
{
    public $titulo;

    public $ref_cod_matricula;

    public $ref_cod_turma;

    public $ref_cod_serie;

    public $ref_cod_escola;

    public $ref_cod_disciplina;

    public $observacao;

    public function Gerar()
    {
        $this->titulo = 'Disciplina de dependência - Detalhe';

        $this->ref_cod_disciplina = $_GET['ref_cod_disciplina'];
        $this->ref_cod_matricula = $_GET['ref_cod_matricula'];
        $this->ref_cod_serie = $_GET['ref_cod_serie'];
        $this->ref_cod_disciplina = $_GET['ref_cod_disciplina'];
        $this->ref_cod_escola = $_GET['ref_cod_escola'];

        $tmp_obj = new clsPmieducarDisciplinaDependencia(
            ref_cod_matricula: $this->ref_cod_matricula,
            ref_cod_serie: $this->ref_cod_serie,
            ref_cod_escola: $this->ref_cod_escola,
            ref_cod_disciplina: $this->ref_cod_disciplina
        );

        $registro = $tmp_obj->detalhe();

        if (!$registro) {
            $this->simpleRedirect('educar_disciplina_dependencia_lst.php?ref_cod_matricula=' . $this->ref_cod_matricula);
        }

        $obj_serie = new clsPmieducarSerie($this->ref_cod_serie);
        $det_serie = $obj_serie->detalhe();
        $registro['ref_ref_cod_serie'] = $det_serie['nm_serie'];

        // Dados da matrícula
        $obj_ref_cod_matricula = new clsPmieducarMatricula;
        $matricula = $obj_ref_cod_matricula->lista($this->ref_cod_matricula);
        $detalhe_aluno = array_shift($matricula);

        $obj_aluno = new clsPmieducarAluno;
        $lista = $obj_aluno->lista(
            int_cod_aluno: $detalhe_aluno['ref_cod_aluno'],
            int_ativo: 1
        );
        $det_aluno = array_shift($lista);

        $obj_escola = new clsPmieducarEscola(
            cod_escola: $this->ref_cod_escola,
            bloquear_lancamento_diario_anos_letivos_encerrados: 1
        );
        $obj_escola->detalhe();

        $nm_aluno = $det_aluno['nome_aluno'];

        // Dados do curso
        $obj_ref_cod_curso = new clsPmieducarCurso($detalhe_aluno['ref_cod_curso']);
        $det_ref_cod_curso = $obj_ref_cod_curso->detalhe();
        $registro['ref_cod_curso'] = $det_ref_cod_curso['nm_curso'];

        if ($registro['ref_cod_matricula']) {
            $this->addDetalhe(['Matricula', $registro['ref_cod_matricula']]);
        }

        if ($nm_aluno) {
            $this->addDetalhe(['Aluno', $nm_aluno]);
        }

        if ($registro['ref_cod_curso']) {
            $this->addDetalhe(['Curso', $registro['ref_cod_curso']]);
        }

        if ($registro['ref_ref_cod_serie']) {
            $this->addDetalhe(['Série', $registro['ref_ref_cod_serie']]);
        }

        if ($registro['ref_cod_disciplina']) {
            $componenteMapper = new ComponenteCurricular_Model_ComponenteDataMapper;
            $componente = $componenteMapper->find($registro['ref_cod_disciplina']);
            $this->addDetalhe(['Componente Curricular', $componente->nome]);
        }

        if ($registro['observacao']) {
            $this->addDetalhe(['Observação', $registro['observacao']]);
        }

        $obj_permissoes = new clsPermissoes;

        if ($obj_permissoes->permissao_cadastra(int_processo_ap: 578, int_idpes_usuario: $this->pessoa_logada, int_soma_nivel_acesso: 7)) {
            $this->url_novo = sprintf(
                'educar_disciplina_dependencia_cad.php?ref_cod_matricula=%d',
                $this->ref_cod_matricula
            );
            $this->url_editar = sprintf(
                'educar_disciplina_dependencia_cad.php?ref_cod_matricula=%d&ref_cod_disciplina=%d',
                $registro['ref_cod_matricula'],
                $registro['ref_cod_disciplina']
            );
        }

        $this->url_cancelar = 'educar_disciplina_dependencia_lst.php?ref_cod_matricula=' . $this->ref_cod_matricula;
        $this->largura = '100%';

        $this->breadcrumb(currentPage: 'Disciplinas de dependência', breadcrumbs: [
            url('intranet/educar_index.php') => 'Escola',
        ]);
    }

    public function Formular()
    {
        $this->title = 'Disciplina dependência';
        $this->processoAp = 578;
    }
};
