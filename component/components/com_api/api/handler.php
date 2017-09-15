<?php
require_once 'handler/interface.php';

class ApiHandler implements ApiHandlerInterface
{
    /**
     * @var string
     */
    protected $component = '';

    /**
     * @var string
     */
    protected $requestType = '';

    /**
     * @var object
     */
    protected $model;

    /**
     * @var string
     */
    protected $methodName = '';

    /**
     * @var array
     */
    protected $allowedMethods = [];

    /**
     * @var JInput
     */
    protected $input;

    /**
     * @param JInput $input
     * @return mixed
     */
    public function handle(JInput $input)
    {
        $this->input = $input;
        $this->component = $input->get('component');
        $this->requestType = $input->getMethod();

        $modelName = $input->getCmd('model');
        $id = $this->getId($input);
        $this->model = $this->getModel($modelName, $id);

        try {
            $data = $this->makeMethodCallback($this->requestType, $this->model);
        } catch (Exception $e) {
            $data = ['error' => $e->getMessage()];
        }

        return $data;
    }

    /**
     * @param JInput $input
     * @return int
     */
    protected function getId(JInput $input)
    {
        return $input->getInt('id', 0);
    }

    /**
     * @param string $requestType
     * @param object $model
     * @return string
     */
    protected function makeMethodCallback(string $requestType, $model)
    {
        $requestType = strtolower($requestType);
        switch ($requestType) {
            case 'delete':
                $methodName = 'delete';
                $methodArguments = []; // @todo
                break;

            case 'put':
                $methodName = 'save';
                $methodArguments = []; // @todo
                break;

            case 'get':
            default:
                $methodName = $this->getMethodFromRequest($model);
        }

        print_r($this->allowedMethods);
        echo $model;exit;
        if (!in_array($methodName, $this->allowedMethods[$model])) {
            throw new Exception('Method is not allowed');
        }

        return $model->$methodName($methodArguments);
    }

    protected function getMethodFromRequest($model)
    {
        $methodFromRequest = $this->input->getString('method');
        if (empty($methodFromRequest)) {
            return false;
        }

        if (!method_exists($model, $methodFromRequest)) {
            throw new Exception('Method ');
        }

        if (!is_callable([$model, $methodFromRequest])) {
            return false;
        }

        return $methodFromRequest;
    }

    protected function getModel(string $modelName, int $id = 0)
    {
        $modelName = $this->determineModelNameFromRequest($modelName, $id);
        $modelFile = $this->getModelFile($modelName);
        include_once $modelFile;

        $modelClassName = ucfirst($this->component) . 'Model' . ucfirst($modelName);

        if (!class_exists($modelClassName)) {
            throw new RuntimeException(JText::_('COM_API_MODEL_CLASS_NOT_FOUND'));
        }

        $model = new $modelClassName;

        return $model;
    }

    protected function getModelFile(string $modelName)
    {
        $modelFile = JPATH_SITE . '/components/com_' . $this->component . '/models/' . $modelName . '.php';

        if (!JFile::exists($modelFile) && JFactory::getUser()->authorise('core.options')) {
            $modelFile = JPATH_ADMINISTRATOR . '/components/com_' . $this->component . '/models/' . $modelName . '.php';
        }

        if (!JFile::exists($modelFile)) {
            throw new RuntimeException(JText::_('COM_API_MODEL_FILE_NOT_FOUND'));
        }

        return $modelFile;
    }

    protected function determineModelNameFromRequest(string $modelName, int $id = 0): string
    {
        $modelName = strtolower($modelName);

        if ($id > 0) {
            $modelName = \Joomla\String\Inflector::getInstance()->toSingular($modelName);
        }

        return $modelName;
    }
}
