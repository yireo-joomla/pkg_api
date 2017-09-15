<?php
/**
 * @package    Api
 *
 * @author     jisse <your@email.com>
 * @copyright  A copyright
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://your.url.com
 */

defined('_JEXEC') or die;

/**
 * Api Controller.
 *
 * @package  api
 * @since    1.0
 */
class ApiController extends JControllerLegacy
{
    private $app;

    public function __construct($config = array())
    {
        $this->app = JFactory::getApplication();

        return parent::__construct($config);
    }

    public function execute($task)
    {
        /** @var ApiHandlerInterface $handler */
        $handler = $this->getApiHandler();
        $data = $handler->handle($this->app->input);

        if (is_array($data) && !empty($data['error'])) {
            header('HTTP/1.0 503 dfsd');
        }

        $this->json($data);
    }

    private function json($data)
    {
        header('Content-Type: application/json');
        echo new JResponseJson($data);
        exit;
    }

    private function getApiHandler()
    {
        $componentName = $this->app->input->getCmd('component');

        if (empty($componentName)) {
            throw new InvalidArgumentException(JText::_('COM_API_NO_COMPONENT_SPECIFIED'));
        }

        if ($this->isApiAllowed($componentName) === false) {
            throw new RuntimeException(JText::_('COM_API_HANDLER_NOT_ALLOWED'));
        }

        $handlerFile = $this->getApiHandlerFilePerComponent($componentName);

        require_once $handlerFile;

        $handlerClassName = $this->getApiHandlerClassName($componentName);

        return new $handlerClassName;
    }

    private function getApiHandlerClassName(string $componentName): string
    {
        $handlerClassName = ucfirst($componentName) . 'ApiHandler';

        if (!class_exists($handlerClassName)) {
            $handlerClassName = 'ApiHandler';
        }

        return $handlerClassName;
    }

    private function isApiAllowed(string $componentName): bool
    {
        if (JComponentHelper::isEnabled('com_' . $componentName) === false) {
            return false;
        }

        return true;
    }

    private function isValidToken()
    {
        return false;
    }

    private function isAuthorized()
    {
        if (JFactory::getUser()->authorise('core.options') === true) {
            return true;
        }

        if ($this->isValidToken() === true) {
            return true;
        }

        return false;
    }

    /**
     * @param string $componentName
     * @return string
     */
    private function getApiHandlerFilePerComponent(string $componentName): string
    {
        $template = $this->app->getTemplate();
        $handlerFile = JPATH_SITE . '/templates/' . $template . '/html/com_api/handler/' . $componentName . '.php';
        if (JFile::exists($handlerFile)) {
            return $handlerFile;
        }

        $handlerFile = JPATH_SITE . '/components/com_api/api/handler/' . $componentName . '.php';
        if (JFile::exists($handlerFile)) {
            return $handlerFile;
        }

        $handlerFile = JPATH_SITE . '/components/com_' . $componentName . '/api.php';
        if (JFile::exists($handlerFile)) {
            return $handlerFile;
        }

        // @todo: The following has been deemed dangerous, even though it is very cool
        $handlerFile = JPATH_COMPONENT . '/api/handler.php';
        if (JFile::exists($handlerFile) && $this->isAuthorized()) {
            return $handlerFile;
        }

        // @todo: Create custom exception class
        throw new \Exception('No Handler file found');
    }
}
