<?php

defined('_JEXEC') or die;

class ApiViewDashboard extends JViewLegacy
{
	protected $sidebar = '';

	public function display($tpl = null)
	{
		$this->toolbar();

		$this->sidebar = JHtmlSidebar::render();

		return parent::display($tpl);
	}

	private function toolbar()
	{
		JToolBarHelper::title(JText::_('COM_API_MENU_DASHBOARD'), 'info');

		JHelperContent::getActions('com_api');
		$user  = JFactory::getUser();

		// Options button.
		if ($user->authorise('core.admin', 'com_api') || $user->authorise('core.options', 'com_api'))
		{
			JToolbarHelper::preferences('com_api');
		}

	}
}