<?php
require_once 'handler/interface.php';

class ApiHandler implements ApiHandlerInterface
{
    protected $component = '';

    protected $requestType = '';

    /**
     * @var object
     */
    protected $model;

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
        $id = $input->getInt('id', 0);
        $this->model = $this->getModel($modelName, $id);

        try {
            $data = $this->makeMethodCallback($this->requestType, $this->model);
        } catch (Exception $e) {
            $data = ['error' => $e->getMessage()];
        }

        return $data;
    }

    /**
     * @param string $requestType
     * @param object $model
     * @return string
     */
    protected function makeMethodCallback(string $requestType, $model)
    {
        switch($requestType) {
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
                if ($this->input->getInt('id') && method_exists($model, 'getItem')) {
                    return 'getItem';
                }

                if (method_exists($model, 'getItems')) {
                    return 'getItems';
                }

                $methodName = 'getData';
        }

        return $model->$methodName($methodArguments);
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

    protected function determineModelNameFromRequest(string $modelName, int $id = 0) : string
    {
        $modelName = strtolower($modelName);

        if ($id > 0) {
            $modelName = \Joomla\String\Inflector::getInstance()->toSingular($modelName);
        }

        return $modelName;
    }
}
