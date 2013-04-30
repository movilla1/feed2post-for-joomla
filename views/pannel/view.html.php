<?php
/**
 * Feed2post v3.0
 * @author Mario O. Villarroel
 * @copyright 2010 - Elcan Software
 * @license GPLv2
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');
require_once (JPATH_COMPONENT_ADMINISTRATOR . DS . 'helper.php');
class Feed2PostViewPannel extends JView
{
    function display($tpl = null)
    {
        if (is_16()) {
            $option=JRequest::getCmd('option');
        } else {
            global $option;
        }
        $this->assignRef("option",$option);
        parent::display();
    }
}
