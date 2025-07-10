<?php
class ViewController extends Core_Controller_Page_ViewController
{
    protected $_dataMapper = 'TabelaArredondamento_Model_TabelaDataMapper';

    // Sem tradução aqui, só string literal
    protected $_titulo = 'Detalhes da tabela de arredondamento';

    protected $_processoAp = 949;

    // Definir sem tradução na propriedade
    protected $_tableMap = [
        'Nome' => 'nome',
        'Tipo nota' => 'tipoNota',
    ];

    protected function _preRender()
    {
        parent::_preRender();

        // Traduzir o título aqui
        $this->_titulo = __('Detalhes da tabela de arredondamento');

        // Traduzir as chaves do _tableMap, se desejar
        $this->_tableMap = [
            __('Nome') => 'nome',
            __('Tipo nota') => 'tipoNota',
        ];

        $this->breadcrumb(
            __('Detalhe da tabela de arredondamento'),
            [
                url('intranet/educar_index.php') => __('Escola'),
            ]
        );
    }
}
