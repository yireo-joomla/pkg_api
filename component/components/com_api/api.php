<?php
/**
 * @package    pkg_api
 *
 * @author     jisse <your@email.com>
 * @copyright  A copyright
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://your.url.com
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.file');

require_once JPATH_COMPONENT . '/api/handler.php';

$controller = JControllerLegacy::getInstance('api');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
