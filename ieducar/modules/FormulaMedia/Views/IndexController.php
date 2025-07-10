<?php

use App\Models\LegacyAverageFormula;

class IndexController extends Core_Controller_Page_ListController
{
    protected $_dataMapper = 'FormulaMedia_Model_FormulaDataMapper';

    protected $_titulo;

    protected $_processoAp = 948;

    protected $_tableMap;

    public function __construct()
    {
        parent::__construct();

        // Inicializa as traduções aqui, em runtime
        $this->_titulo = __('Listagem de fórmulas de cálculo de média');

        $this->_tableMap = [
            __('Nome') => 'nome',
            __('Fórmula de cálculo') => 'formula_media',
            __('Tipo fórmula') => 'tipo_formula',
        ];
    }

    public function Gerar()
    {
        parent::Gerar();

        $this->campoTexto(
            nome: 'nome',
            campo: __('Nome'),
            valor: request('nome'),
        );

        $this->campoTexto(
            nome: 'formula_media',
            campo: __('Fórmula de cálculo'),
            valor: request('formula_media'),
        );

        $tipoFormula = collect(FormulaMedia_Model_TipoFormula::getInstance()->getEnums())
            ->mapWithKeys(fn($v, $k) => [$k => __($v)])
            ->prepend(__('Todos os tipos'), '');

        $this->campoLista(
            nome: 'tipo_formula',
            campo: __('Tipo de fórmula'),
            valor: $tipoFormula,
            default: request('tipo_formula')
        );
    }

    public function getEntries()
    {
        return LegacyAverageFormula::query()
            ->when(request('nome'), fn($q, $nome) => $q->whereRaw('unaccent(nome) ~* unaccent(?)', $nome))
            ->when(request('formula_media'), function ($q, $formulaMedia) {
                return $q->where('formula_media', 'ilike', "%{$formulaMedia}%");
            })
            ->when(request('tipo_formula'), fn($q, $tipoFormula) => $q->where('tipo_formula', $tipoFormula))
            ->orderBy('nome')
            ->get()
            ->map(function ($averageFormula) {
                $tipoFormula = FormulaMedia_Model_TipoFormula::getInstance()->getEnums();

                $averageFormula->tipo_formula = __($tipoFormula[$averageFormula->tipo_formula]);

                return $averageFormula;
            });
    }

    protected function _preRender()
    {
        parent::_preRender();

        $this->breadcrumb(
            __('Listagem de fórmulas de média'),
            [
                url('intranet/educar_index.php') => __('Escola'),
            ]
        );
    }
}
