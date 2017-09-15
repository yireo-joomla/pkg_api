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

use JFactory as Factory;

jimport('joomla.filesystem.file');
JLoader::registerNamespace('Api', __DIR__, false, false, 'psr4');

$controller = new Api\Controller;
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();
