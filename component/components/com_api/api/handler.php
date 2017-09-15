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
     * @return mixed
     */
    protected function makeMethodCallback(string $requestType, $model)
    {
        $methodArguments = [];
        $requestType = strtolower($requestType);
        switch ($requestType) {
            case 'delete':
                $methodName = 'delete';
                break;

            case 'put':
                $methodName = 'save';
                break;

            case 'get':
            default:
                $methodName = $this->getMethodName($model);
        }

        if ($methodName === 'info') {
            return $this->allowedMethods;
        }

        if (!$this->isMethodAllowed($methodName, $model)) {
            return false;
        }

        return $model->$methodName($methodArguments);
    }

    protected function isMethodAllowed($methodName, $model)
    {
        $modelName = get_class($model);
        if (!isset($this->allowedMethods[$modelName]) || !in_array($methodName, $this->allowedMethods[$modelName])) {
            throw new Exception('Method is not allowed');
        }

        if (!method_exists($model, $methodName)) {
            throw new Exception('Method ');
        }

        if (!is_callable([$model, $methodName])) {
            return false;
        }

        return true;
    }

    protected function getMethodName($model)
    {
        $methodName = $this->input->getString('method');
        if (empty($methodName)) {
            return false;
        }

        return $methodName;
    }

    protected function getModel(string $modelName, int $id = 0)
    {
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
}
