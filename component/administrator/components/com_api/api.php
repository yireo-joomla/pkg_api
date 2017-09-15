<?php
defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_api'))
{
return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

// Execute the task
$controller = JControllerLegacy::getInstance('api');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
