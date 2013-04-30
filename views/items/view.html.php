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
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'items.php');
class Feed2PostViewItems extends JView
{
    function __construct($config=array()) {
       $mdl=new Feed2postModelItems();
       $this->setModel($mdl,true); 
       parent::__construct($config);
    }
    
    function display($tpl = null)
    {
        $mainframe=&JFactory::getApplication();
        if (is_16())
            $option=JRequest::getCmd('option');
        else 
            global $option;
        jimport('joomla.html.pagination');
        $filter_order = $mainframe->getUserStateFromRequest("$option.filter_order", 'filter_order', 'ordering', 'word');
        $limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        $limitstart = $mainframe->getUserStateFromRequest("$option.itemList.limitstart",'limitstart', 0, 'int');
        $model = &$this->getModel(); // Categories
        $model->setOrder($filter_order);
        $model->setLimits($limitstart,$limit);
        $rows = $model->getData();
        $total = count($rows);
        $pagination = new JPagination($total, $limitstart, $limit);
        $this->assignRef("option",$option);
        $this->assignRef('rows', $rows);
        $this->assignRef('page',$pagination);
        parent::display($tpl);
    }
}
