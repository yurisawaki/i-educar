<?php

class EditController extends Core_Controller_Page_EditController
{
    protected $_dataMapper = 'TabelaArredondamento_Model_TabelaDataMapper';

    protected $_titulo = 'Registro de tabla de redondeo de notas';

    protected $_processoAp = 949;

    protected $_nivelAcessoOption = App_Model_NivelAcesso::INSTITUCIONAL;

    protected $_saveOption = true;

    protected $_deleteOption = false;

    protected $valor_minimo = [];

    protected $valor_maximo = [];

    protected $_formMap = [
        'instituicao' => [
            'label' => 'Institución',
            'help' => '',
        ],
        'nome' => [
            'label' => 'Nombre',
            'help' => 'Un nombre para la tabla. Ejemplo: "<em>Tabla genérica de conceptos</em>".',
        ],
        'tipoNota' => [
            'label' => 'Tipo de nota',
            'help' => '',
        ],
        'arredondarNota' => [
            'label' => 'Redondear nota de la etapa',
            'help' => '',
        ],
        'valor_nome' => [
            'label' => 'Etiqueta de la nota:',
            'help' => 'Ejemplos: A, B, C (conceptuales)<br /><b>6,5</b>, <b>7,5</b> (numéricas)',
        ],
        'valor_descricao' => [
            'label' => '<span style="padding-left: 10px"></span>Descripción:',
            'help' => 'Ejemplos: Bueno, Regular, En proceso.',
        ],
        'valor_observacao' => [
            'label' => '<span style="padding-left: 10px"></span>Observación:',
            'help' => 'Ejemplos: Cuando el/la alumno/a desarrolla las actividades sin dificultades.',
        ],
        'valor_valor_minimo' => [
            'label' => '<span style="padding-left: 10px"></span>Valor mínimo:',
            'help' => 'El valor numérico mínimo de la nota.',
        ],
        'valor_valor_maximo' => [
            'label' => '<span style="padding-left: 10px"></span>Valor máximo:',
            'help' => 'El valor numérico máximo de la nota.',
        ],
        'acao' => [
            'label' => '<span style="padding-left: 10px"></span>Acción:',
            'help' => 'La acción de redondeo de la nota.',
        ],
        'casa_decimal' => [
            'label' => '<span style="padding-left: 10px"></span>Casa decimal:',
            'help' => 'La casa decimal exacta a la que la nota debe ser redondeada.',
        ],
        'casa_decimal_exata' => [
            'label' => '<span style="padding-left: 10px"></span>Casa decimal exacta:',
            'help' => 'La casa decimal que será redondeada.',
        ],
    ];

    protected $_valores = [];

    protected function _setValores(array $valores = [])
    {
        foreach ($valores as $key => $valor) {
            $this->_valores[$valor->id] = $valor;
        }

        return $this;
    }

    protected function _getValores()
    {
        return $this->_valores;
    }

    protected function _getValor($id)
    {
        return isset($this->_valores[$id]) ? $this->_valores[$id] : null;
    }

    protected function _preConstruct()
    {
        if (isset($this->getRequest()->id) && $this->getRequest()->id > 0) {
            $this->setEntity(
                $this->getDataMapper()->find($this->getRequest()->id)
            );

            $this->_setValores(
                $this->getDataMapper()
                    ->findTabelaValor($this->getEntity())
            );
        }
    }

    public function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadJavascript(
            $this,
            '/vendor/legacy/RegraAvaliacao/Assets/Javascripts/TabelaArredondamento.js'
        );

        $nomeMenu = $this->getRequest()->id == null ? 'Registrar' : 'Editar';

        $this->breadcrumb("$nomeMenu tabla de redondeo", [
            url('intranet/educar_index.php') => 'Escuela',
        ]);
    }

    public function Gerar()
    {
        $this->campoOculto('id', $this->getEntity()->id);

        $instituicoes = App_Model_IedFinder::getInstituicoes();
        $this->campoLista(
            'instituicao',
            $this->_getLabel('instituicao'),
            $instituicoes,
            $this->getEntity()->instituicao
        );

        $this->campoTexto(
            'nome',
            $this->_getLabel('nome'),
            $this->getEntity()->nome,
            40,
            50,
            true,
            false,
            false,
            $this->_getHelp('nome')
        );

        $notaTipoValor = RegraAvaliacao_Model_Nota_TipoValor::getInstance();
        $notaTipos = $notaTipoValor->getEnums();
        unset($notaTipos[RegraAvaliacao_Model_Nota_TipoValor::NENHUM]);
        unset($notaTipos[RegraAvaliacao_Model_Nota_TipoValor::NUMERICACONCEITUAL]);

        if ($this->getEntity()->id != '') {
            $this->campoTexto(
                'tipoNota',
                $this->_getLabel('tipoNota'),
                $notaTipos[$this->getEntity()->get('tipoNota')],
                40,
                40,
                false,
                false,
                false,
                '',
                '',
                '',
                '',
                true
            );
        } else {
            $this->campoRadio(
                'tipoNota',
                $this->_getLabel('tipoNota'),
                $notaTipos,
                $this->getEntity()->get('tipoNota'),
                '',
                $this->_getHelp('tipoNota')
            );
        }

        $this->campoLista(
            'arredondarNota',
            $this->_getLabel('arredondarNota'),
            [0 => 'No', 1 => 'Sí'],
            $this->getEntity()->get('arredondarNota')
        );

        if (!$this->getEntity()->isNew()) {
            $this->campoQuebra();

            if ($this->getEntity()->get('tipoNota') == RegraAvaliacao_Model_Nota_TipoValor::CONCEITUAL) {
                $this->carregaCamposNotasConceituais();
            } elseif ($this->getEntity()->get('tipoNota') == RegraAvaliacao_Model_Nota_TipoValor::NUMERICA) {
                $this->carregaCamposNotasNumericas();
            }

            $this->campoQuebra();
        }
    }

    private function carregaCamposNotasConceituais()
    {
        $valores = $this->getDataMapper()->findTabelaValor($this->getEntity());

        for ($i = 0, $loop = count($valores); $i < $loop; $i++) {
            $valorNota = $valores[$i];
            $this->tabela_arredondamento_valor[$i][] = $valorNota->id;
            $this->tabela_arredondamento_valor[$i][] = $valorNota->nome;
            $this->tabela_arredondamento_valor[$i][] = $valorNota->descricao;
            $this->tabela_arredondamento_valor[$i][] = $valorNota->observacao;
            $this->tabela_arredondamento_valor[$i][] = $valorNota->valorMinimo;
            $this->tabela_arredondamento_valor[$i][] = $valorNota->valorMaximo;
        }

        $this->campoTabelaInicio(
            'tabela_arredondamento',
            'Notas para redondeo',
            [
                'ID',
                'Etiqueta de la nota',
                'Descripción',
                'Observación',
                'Valor mínimo',
                'Valor máximo',
            ],
            $this->tabela_arredondamento_valor
        );

        $this->campoTexto(
            'valor_id',
            'id',
            $valorNota->id,
            5,
            5,
            false,
            false,
            false
        );

        $this->campoTexto(
            'valor_nome',
            'valor_nome',
            $valorNota->nome,
            5,
            5,
            true,
            false,
            false,
            $this->_getHelp('valor_nome')
        );

        $this->campoTexto(
            'valor_descricao',
            'valor_descricao',
            $valorNota->descricao,
            15,
            50,
            true,
            false,
            false,
            $this->_getHelp('valor_descricao')
        );

        $this->campoTexto(
            'valor_observacao',
            'valor_observacao',
            $valorNota->observacao,
            null,
            125,
            false,
            false,
            false,
            $this->_getHelp('valor_observacao')
        );

        $this->campoTexto(
            'valor_minimo',
            'valor_valor_minimo',
            $valorNota->valorMinimo,
            6,
            6,
            true,
            false,
            false,
            $this->_getHelp('valor_valor_minimo')
        );

        $this->campoTexto(
            'valor_maximo',
            'valor_valor_maximo',
            $valorNota->valorMaximo,
            6,
            6,
            true,
            false,
            false,
            $this->_getHelp('valor_valor_maximo')
        );

        $this->campoTabelaFim();
    }

    private function carregaCamposNotasNumericas()
    {
        $valores = $this->getDataMapper()->findTabelaValor($this->getEntity());

        for ($i = 0; $i <= 9; $i++) {
            $valorNota = $valores[$i];
            $acao = match ($valorNota->acao) {
                'Arredondar para o número inteiro imediatamente inferior' => 1,
                'Arredondar para o número inteiro imediatamente superior' => 2,
                'Arredondar para a casa decimal específica' => 3,
                default => 0,
            };

            $this->tabela_arredondamento_valor[$i][] = $valorNota->id;
            $this->tabela_arredondamento_valor[$i][] = $i;
            $this->tabela_arredondamento_valor[$i][] = $i;
            $this->tabela_arredondamento_valor[$i][] = $acao;
            $this->tabela_arredondamento_valor[$i][] = $valorNota->casaDecimalExata;
        }

        $this->campoTabelaInicio(
            'tabela_arredondamento_numerica',
            'Notas para redondeo',
            [
                'ID',
                'Nombre',
                'Casa decimal',
                'Acción',
                'Casa decimal exacta',
            ],
            $this->tabela_arredondamento_valor
        );

        $this->campoTexto(
            'valor_id',
            'id',
            $valorNota->id,
            5,
            5,
            false,
            false,
            false
        );

        $this->campoTexto(
            'valor_nome',
            'casa_decimal',
            $valorNota->nome,
            1,
            1,
            false,
            false,
            false,
            '',
            '',
            '',
            'onKeyUp',
            false
        );

        $this->campoTexto(
            'valor_nome_fake',
            'casa_decimal_fake',
            $valorNota->nome,
            1,
            1,
            false,
            false,
            false,
            '',
            '',
            '',
            'onKeyUp',
            true
        );

        $tipoArredondamentoMedia = TabelaArredondamento_Model_TipoArredondamentoMedia::getInstance();

        $this->campoLista(
            'valor_acao',
            'acao',
            $tipoArredondamentoMedia->getEnums(),
            $valorNota->acao,
            '',
            false,
            $this->_getHelp('tipoRecuperacaoParalela'),
            '',
            false,
            false
        );

        $this->campoTexto(
            'valor_casa_decimal_exata',
            'valor_casa_decimal_exata',
            $valorNota->casaDecimalExata,
            1,
            1,
            false,
            false,
            false,
            '',
            '',
            '',
            'onKeyUp',
            false
        );

        $this->campoTabelaFim();
    }

    protected function _save()
    {
        if (isset($this->getRequest()->id) && $this->getRequest()->id > 0) {
            $this->setEntity($this->getDataMapper()->find($this->getRequest()->id));
            $entity = $this->getEntity();
        }

        $loop = is_array($this->valor_id) ? count($this->valor_id) : 0;

        for ($i = 0; $i < $loop; $i++) {
            if (($this->valor_maximo[$i] >= 100) || ($this->valor_minimo[$i] >= 100)) {
                $this->mensagem = 'Error en el formulario';

                return false;
            }
        }

        if (!$this->validatesRange($this->valor_minimo, $this->valor_maximo)) {
            return false;
        }

        if (!isset($entity)) {
            return parent::_save();
        }

        $entity->arredondarNota = $this->getRequest()->arredondarNota;

        $this->getDataMapper()->save($entity);

        $entity->deleteAllValues();

        for ($i = 0; $i < $loop; $i++) {
            $valores[] = [
                'id' => $this->valor_id[$i],
                'nome' => $this->valor_nome[$i],
                'descricao' => $this->valor_descricao[$i],
                'observacao' => $this->valor_observacao[$i],
                'valor_minimo' => $this->valor_minimo[$i],
                'valor_maximo' => $this->valor_maximo[$i],
                'valor_acao' => $this->valor_acao[$i],
                'valor_casa_decimal_exata' => $this->valor_casa_decimal_exata[$i],
            ];
        }

        $insert = [];

        for ($i = 0; $i < $loop; $i++) {
            $id = $valores[$i]['id'];

            $data = [
                'nome' => $valores[$i]['nome'],
                'descricao' => $valores[$i]['descricao'],
                'observacao' => $valores[$i]['observacao'],
                'valorMinimo' => str_replace(',', '.', $valores[$i]['valor_minimo']),
                'valorMaximo' => str_replace(',', '.', $valores[$i]['valor_maximo']),
                'acao' => $valores[$i]['valor_acao'],
                'casaDecimalExata' => $valores[$i]['valor_casa_decimal_exata'],
            ];

            $instance = new TabelaArredondamento_Model_TabelaValor($data);

            if (!$instance->isNull()) {
                $insert['new_' . $i] = $instance;
            }
        }

        foreach ($insert as $tabelaValor) {
            $tabelaValor->tabelaArredondamento = $entity;

            if ($tabelaValor->isValid()) {
                $this->getDataMapper()
                    ->getTabelaValorDataMapper()
                    ->save($tabelaValor);
            } else {
                $this->mensagem = 'Error en el formulario';

                return false;
            }
        }

        return true;
    }

    protected function validatesRange($minValues = [], $maxValues = [])
    {
        $repeatedValues = count($minValues) !== count(array_unique($minValues));

        if ($repeatedValues) {
            $this->mensagem = 'Error en el formulario. Los valores deben ser diferentes entre los tipos de conceptos.';

            return false;
        }

        $values = array_combine($minValues, $maxValues);
        ksort($values);
        $prevMax = -1;

        foreach ($values as $minValue => $maxValue) {
            if ($minValue > $maxValue) {
                $this->mensagem = 'Error en el formulario. El valor mínimo no puede ser mayor que el valor máximo dentro del mismo concepto.';

                return false;
            }

            if ($minValue <= $prevMax) {
                $this->mensagem = 'Error en el formulario. Números fuera del rango permitido.';

                return false;
            }

            $prevMax = $maxValue;
        }

        return true;
    }
}
