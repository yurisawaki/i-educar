<?php

use App\Models\LegacyEvaluationRule;
use Illuminate\Http\Exceptions\HttpResponseException;

class EditController extends Core_Controller_Page_EditController
{
    protected $_dataMapper = 'FormulaMedia_Model_FormulaDataMapper';

    protected $_titulo;

    protected $_processoAp = 948;

    protected $_nivelAcessoOption = App_Model_NivelAcesso::INSTITUCIONAL;

    protected $_saveOption = true;

    protected $_deleteOption = true;

    protected $_formMap;

    public function __construct()
    {
        parent::__construct();

        $this->_titulo = __('Cadastro de fórmula de cálculo de média');

        $this->_formMap = [
            'instituicao' => [
                'label' => __('Instituição'),
                'help' => '',
            ],
            'nome' => [
                'label' => __('Nome'),
                'help' => '',
            ],
            'formulaMedia' => [
                'label' => __('Fórmula de média final'),
                'help' => __('A fórmula de cálculo.<br />
                   Variáveis disponíveis:<br />
                   &middot; En - Etapa n (de 1 a 10)<br />
                   &middot; Cn - Considera etapa n (de 1 a 10): 1 - Sim, 0 - Não<br />
                   &middot; Et - Total de etapas<br />
                   &middot; Se - Soma das notas das etapas<br />
                   &middot; Rc - Nota da recuperação<br />
                   &middot; RSPN - Recuperação específica n (de 1 a 10)<br />
                   &middot; RSPSN - Soma das etapas ou Recuperação específica (Pega maior) n (de 1 a 10)<br />
                   &middot; RSPMN - Média das etapas ou Média das etapas com Recuperação específica (Pega maior) n (de 1 a 10)<br />
                   Símbolos disponíveis:<br />
                   &middot; (), +, /, *, x<br />
                   &middot; < > ? :<br />
                   A variável "Rc" está disponível apenas<br />
                   quando Tipo de fórmula for "Recuperação".'),
            ],
            'tipoFormula' => [
                'label' => __('Tipo de fórmula'),
                'help' => '',
            ],
            'substituiMenorNotaRc' => [
                'label' => __('Substitui menor nota por recuperação'),
                'help' => __('Substitui menor nota (En) por nota de recuperação (Rc) em ordem descrescente.<br/>
                   Somente substitui quando Rc é maior que En.
                   Ex: E1 = 2, E2 = 3, E3 = 2, Rc = 5.
                   Na fórmula será considerado: E1 = 2, E2 = 3, E3 = 5, Rc = 5.'),
            ],
        ];
    }

    public function _preRender()
    {
        Portabilis_View_Helper_Application::loadJavascript($this, '/vendor/legacy/FormulaMedia/Assets/Javascripts/FormulaMedia.js');

        $nomeMenu = $this->getRequest()->id == null ? __('Cadastrar') : __('Editar');

        $this->breadcrumb(
            "$nomeMenu " . __('fórmula de média'),
            [
                url('intranet/educar_index.php') => __('Escola'),
            ]
        );
    }

    public function Gerar()
    {
        $this->campoOculto('id', $this->getEntity()->id);

        // Instituição
        $instituicoes = App_Model_IedFinder::getInstituicoes();
        $this->campoLista(
            'instituicao',
            $this->_formMap['instituicao']['label'],
            $instituicoes,
            $this->getEntity()->instituicao
        );

        // Nome
        $this->campoTexto(
            'nome',
            $this->_formMap['nome']['label'],
            $this->getEntity()->nome,
            40,
            50,
            true,
            false,
            false,
            $this->_formMap['nome']['help']
        );

        // Fórmula de média
        $this->campoTexto(
            'formulaMedia',
            $this->_formMap['formulaMedia']['label'],
            $this->getEntity()->formulaMedia,
            40,
            200,
            true,
            false,
            false,
            $this->_formMap['formulaMedia']['help']
        );

        // Substitui menor nota
        $this->campoCheck(
            'substituiMenorNotaRc',
            $this->_formMap['substituiMenorNotaRc']['label'],
            $this->getEntity()->substituiMenorNotaRc,
            '',
            false,
            false,
            false,
            $this->_formMap['substituiMenorNotaRc']['help']
        );

        // Tipo de fórmula
        $tipoFormula = FormulaMedia_Model_TipoFormula::getInstance();
        $this->campoRadio(
            'tipoFormula',
            $this->_formMap['tipoFormula']['label'],
            $tipoFormula->getEnums(),
            $this->getEntity()->get('tipoFormula')
        );
    }

    private function usedInExamRule()
    {
        $id = $this->getRequest()->id;

        return LegacyEvaluationRule::where('formula_media_id', $id)
            ->orWhere('formula_recuperacao_id', $id)
            ->exists();
    }

    public function Excluir()
    {
        if ($this->usedInExamRule()) {
            $this->mensagem = __('Não foi possível excluir a fórmula de cálculo de média, pois a mesma possui vínculo com regras de avaliação.');

            return false;
        }

        try {
            parent::Excluir();
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (Throwable) {
            return false;
        }

        return true;
    }

    protected function _save()
    {
        $data = [];

        foreach ($_POST as $key => $val) {
            if (array_key_exists($key, $this->_formMap)) {
                $data[$key] = $val;
            }
        }

        if (!isset($data['substituiMenorNotaRc'])) {
            $data['substituiMenorNotaRc'] = '0';
        }

        if (isset($this->getRequest()->id) && $this->getRequest()->id > 0) {
            $entity = $this->setEntity($this->getDataMapper()->find($this->getRequest()->id));
        }

        if (isset($entity)) {
            $this->getEntity()->setOptions($data);
        } else {
            $this->setEntity($this->getDataMapper()->createNewEntityInstance($data));
        }

        try {
            $this->getDataMapper()->save($this->getEntity());

            return true;
        } catch (Exception) {
            $this->mensagem = __('Erro no preenchimento do formulário.');

            return false;
        }
    }
}
