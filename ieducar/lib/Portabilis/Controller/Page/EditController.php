<?php

class Portabilis_Controller_Page_EditController extends Core_Controller_Page_EditController
{
    protected $_dataMapper;

    protected $_nivelAcessoInsuficiente = '/intranet/index.php?negado=1';

    protected $_titulo = '';

    protected $backwardCompatibility = false;

    public function __construct()
    {
        parent::__construct();
        $this->loadAssets();
    }

    protected function canSave()
    {
        return true;
    }

    public function Gerar()
    {
        throw new Exception('The method \'Gerar\' must be overwritten!');
    }

    protected function save()
    {
        throw new Exception('The method \'save\' must be overwritten!');
    }

    protected function _save()
    {
        $result = false;

        if (!$this->_initNovo()) {
            $this->_initEditar();
        }

        if (!$this->messenger()->hasMsgWithType('error') && $this->canSave()) {
            try {
                $result = $this->save();

                if (is_null($result)) {
                    $result = !$this->messenger()->hasMsgWithType('error');
                } elseif (!is_bool($result)) {
                    throw new Exception("Invalid value returned from '_save' method: '$result', please return null, true or false!");
                }
            } catch (Exception $e) {
                $this->messenger()->append('Erro ao gravar alterações, por favor, tente novamente.', 'error');
                error_log('Erro ao gravar alteracoes: ' . $e->getMessage());

                $result = false;
            }

            $result = $result && !$this->messenger()->hasMsgWithType('error');

            if ($result) {
                $this->messenger()->append('Alterações gravadas com sucesso.', 'success', false, 'success');
            }
        }

        return $result;
    }

    protected function flashMessage()
    {
        if (!$this->hasErrors()) {
            return $this->messenger()->toHtml();
        }

        return '';
    }

    protected function validator()
    {
        if (!isset($this->_validator)) {
            // FIXME #parameters
            $messenger = null;
            $this->_validator = new Portabilis_Validator($messenger);
        }

        return $this->_validator;
    }

    protected function messenger()
    {
        if (!isset($this->_messenger)) {
            $this->_messenger = new Portabilis_Messenger;
        }

        return $this->_messenger;
    }

    protected function loadResourceAssets($dispatcher)
    {
        $rootPath = $_SERVER['DOCUMENT_ROOT'];
        $controllerName = ucwords($dispatcher->getControllerName());
        $actionName = ucwords($dispatcher->getActionName());

        $style = "/vendor/legacy/$controllerName/Assets/Stylesheets/$actionName.css";
        $script = "/vendor/legacy/$controllerName/Assets/Javascripts/$actionName.js";

        if (file_exists($rootPath . $style)) {
            Portabilis_View_Helper_Application::loadStylesheet($this, $style);
        }

        if (file_exists($rootPath . $script)) {
            Portabilis_View_Helper_Application::loadJavascript($this, $script);
        }
    }

    protected function loadAssets()
    {
        Portabilis_View_Helper_Application::loadJQueryFormLib($this);

        $styles = [
            '/vendor/legacy/Portabilis/Assets/Stylesheets/Frontend.css',
            '/vendor/legacy/Portabilis/Assets/Stylesheets/Frontend/Resource.css',
        ];

        Portabilis_View_Helper_Application::loadStylesheet($this, $styles);

        $scripts = [
            '/vendor/legacy/Portabilis/Assets/Javascripts/ClientApi.js',
            '/vendor/legacy/Portabilis/Assets/Javascripts/Validator.js',
            '/vendor/legacy/Portabilis/Assets/Javascripts/Utils.js',
        ];

        if (!$this->backwardCompatibility) {
            $scripts[] = '/vendor/legacy/Portabilis/Assets/Javascripts/Frontend/Resource.js';
        }

        Portabilis_View_Helper_Application::loadJavascript($this, $scripts);
    }

    protected static function mergeOptions($options, $defaultOptions)
    {
        return Portabilis_Array_Utils::merge($options, $defaultOptions);
    }

    protected function fetchPreparedQuery($sql, $options = [])
    {
        return Portabilis_Utils_Database::fetchPreparedQuery($sql, $options);
    }

    protected function getDataMapperFor($packageName, $modelName)
    {
        return (new Portabilis_DataMapper_Utils)->getDataMapperFor($packageName, $modelName);
    }
}
