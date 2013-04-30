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
class Feed2PostViewImports extends JView
{
    function display($tpl = null)
    {
        $db = &JFactory::getDBO();
        if (is_16()) {
            $option=JRequest::getCmd('option');
        } else
            global $option;
        $query = "SELECT id,link,name FROM #__newsfeeds WHERE 1";
        $db->setQuery($query);
        $rows = $db->loadObjectList();
        $this->assignRef("rows",$rows);
        $lists=getListsGen(false);
        $this->assignRef('option',$option);
        $this->assignRef("lists",$lists[0]);
        $this->assignRef("sectioncategories",$lists[1]);
        parent::display();
    }
}