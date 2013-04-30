<?php
/**
 * Feed2post v3.0
 * @author Mario O. Villarroel
 * @copyright 2010 - Elcan Software
 * @license GPLv2
 * 
 */
defined('_JEXEC') or die('Restricted access');
//import component helper.
jimport('joomla.application.component.helper');
require_once (JPATH_COMPONENT_ADMINISTRATOR . DS . 'controller.php');
//require_once (JPATH_COMPONENT_ADMINISTRATOR . DS . 'admin.feed2post.html.php');
$controller = new Feed2PostController();
$controller->registerTask('new', 'edit');
$controller->registerTask('apply', 'save');
$controller->registerTask('apply_new', 'save');
$controller->registerTask("remove","removeSrcItem");

$controller->execute(JRequest::getVar('task', null, 'default', 'cmd'));
$controller->redirect();
?>