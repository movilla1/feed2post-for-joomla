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
require_once ('controller.php');
//require_once (dirname(__FILE__). DS . 'feed2post.html.php');
$cont = new Feed2postSController();

$cont->execute(JRequest::getVar('task', null, 'default', 'cmd'), JRequest::getVar('id'));
$cont->redirect();
?>