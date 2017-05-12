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

        $methodName = $this->getMethodName($this->requestType);

        try {
            $data = $this->model->$methodName();
        } catch (Exception $e) {
            $data = ['error' => $e->getMessage()];
        }

        return $data;
    }

    /**
     * @param $requestType
     * @return string
     */
    protected function getMethodName($requestType)
    {
        switch($requestType) {
            case 'delete':
                $methodName = 'delete';
                break;

            case 'put':
                $methodName = 'save';
                break;

            case 'get':
            default:
                if ($this->input->getInt('id')) {
                    return 'getItem';
                }

                $methodName = 'getItems';
        }

        return $methodName;
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