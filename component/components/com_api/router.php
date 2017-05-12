<?php
/**
 * @todo: Parameters "component" & "model" and other parameters (ID, etc)
 * Structure: http://JOOMLA/MENU_ITEM_ALIAS/COMPONENT/MODEL/{ID}
 * Example: http://JOOMLA/api/content/articles/
 */

defined('_JEXEC') or die;


class ApiRouter
{
    public function build(array &$query) : array
    {
        $segments = [];

        return $segments;
    }

    public function parse(array &$segments): array
    {
        $vars = [];

        $vars['component'] = array_shift($segments);
        $vars['model'] = array_shift($segments);

        if (!empty($segments) && is_numeric($segments[0]) && $segments[0] > 0) {
            $vars['id'] = $segments[0];
        }

        return $vars;
    }

}

function apiBuildRoute(&$query)
{
    $router = new ApiRouter;

    return $router->build($query);
}

function apiParseRoute($segments)
{
    $router = new ApiRouter;

    return $router->parse($segments);
}