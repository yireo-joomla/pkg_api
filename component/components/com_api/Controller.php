<?php
/**
 * @package    Api
 *
 * @author     jisse <your@email.com>
 * @copyright  A copyright
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://your.url.com
 */

namespace Api;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Application\CMSApplication;
use JFactory as Factory;
use Api\Handler\HandlerInterface;
use JFile as File;
use JText as Text;
use JComponentHelper as ComponentHelper;
use Joomla\CMS\Response\JsonResponse as JsonResponse;
use InvalidArgumentException;
use RuntimeException;
use Exception;

/**
 * Api Controller.
 *
 * @package  api
 */
class Controller extends BaseController
{
    /**
     * @var CMSApplication
     */
    private $app;

    /**
     * ApiController constructor.
     * @param array $config
     */
    public function __construct($config = array())
    {
        $this->app = Factory::getApplication();

        return parent::__construct($config);
    }

    /**
     * @param string $task
     *
     * @return void
     */
    public function execute($task)
    {
        /** @var HandlerInterface $handler */
        $handler = $this->getApiHandler();
        $data = $handler->handle($this->app->input);

        if (is_array($data) && !empty($data['error'])) {
            header('HTTP/1.0 503 dfsd');
        }

        $this->json($data);
    }

    /**
     * @param mixed $data
     */
    private function json($data)
    {
        header('Content-Type: application/json');
        echo new JsonResponse($data);
        exit;
    }

    /**
     * @return HandlerInterface
     * @throws Exception
     */
    private function getApiHandler() : HandlerInterface
    {
        $componentName = $this->app->input->getCmd('component');

        if (empty($componentName)) {
            throw new InvalidArgumentException(Text::_('COM_API_NO_COMPONENT_SPECIFIED'));
        }

        if ($this->isApiAllowed($componentName) === false) {
            throw new RuntimeException(Text::_('COM_API_HANDLER_NOT_ALLOWED'));
        }

        $handlerClassName = $this->getApiHandlerClassName($componentName);

        if (!class_exists($handlerClassName)) {
            $handlerFile = $this->getApiHandlerFilePerComponent($componentName);
            require_once $handlerFile;
        }

        if (!class_exists($handlerClassName)) {
            throw new Exception('Class '.$handlerClassName.' not found');
        }

        return new $handlerClassName;
    }

    /**
     * @param string $componentName
     * @return string
     */
    private function getApiHandlerClassName(string $componentName): string
    {
        $handlerClassName = '\Api\Handler\\'.ucfirst($componentName);

        if (!class_exists($handlerClassName)) {
            $handlerClassName = \Api\Handler::class;
        }

        return $handlerClassName;
    }

    /**
     * @param string $componentName
     * @return bool
     */
    private function isApiAllowed(string $componentName): bool
    {
        if (ComponentHelper::isEnabled('com_' . $componentName) === false) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    private function isValidToken()
    {
        return false;
    }

    /**
     * @return bool
     */
    private function isAuthorized()
    {
        if (Factory::getUser()->authorise('core.options') === true) {
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
     * @throws Exception
     */
    private function getApiHandlerFilePerComponent(string $componentName): string
    {
        $template = $this->app->getTemplate();
        $handlerFile = JPATH_SITE . '/templates/' . $template . '/html/com_api/handler/' . $componentName . '.php';
        if (File::exists($handlerFile)) {
            return $handlerFile;
        }

        $handlerFile = JPATH_SITE . '/components/com_api/Handler/' . $componentName . '.php';
        if (File::exists($handlerFile)) {
            return $handlerFile;
        }

        $handlerFile = JPATH_SITE . '/components/com_' . $componentName . '/api.php';
        if (File::exists($handlerFile)) {
            return $handlerFile;
        }

        $handlerFile = JPATH_COMPONENT . '/Handler.php';
        if (File::exists($handlerFile) && $this->isAuthorized()) {
            return $handlerFile;
        }

        throw new \Exception('No Handler file found');
    }
}
