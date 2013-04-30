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
class Feed2PostViewSingle extends JView
{
    function display($tpl = null)
    {
        $mainframe=&JFactory::getApplication();
        if (is_16())
            $option=JRequest::getCmd('option');
        else
            global $option;
        $row = &JTable::getInstance('Feed2post');
        $cid = JRequest::getVar('cid', array(0), '', 'array');
        JArrayHelper::toInteger($cid, array(0));
        $id = JRequest::getVar('id', $cid[0], '', 'int');
        //$nullDate = $db->getNullDate();
        $user = &JFactory::getUser();

        if ($id > 0) {
            $row->load($id);
            if (JTable::isCheckedOut($user->get('id'), $row->checked_out)) {
                $msg = JText::sprintf('DESCBEINGEDITTED', JText::_('THE_ITEM'), $row->feed_url);
                $mainframe->redirect('index.php?option='.$option, $msg);
            }
            $row->checkout($user->get('id'));
        } else {
            $values = getDefaults();
            $row->feed_url = "";
            $row->advert = $values->advert;
            $row->published = $values->published;
            $row->fulltext = $values->fulltext;
            $row->iframelinks = $values->iframelinks;
            $row->ignoreitem = $values->ignoreitem;
            $row->height = $values->height;
            $row->width = $values->width;
            $row->marginheight = $values->marginheight;
            $row->marginwidth = $values->marginwidth;
            $row->scrolling = $values->scrolling;
            $row->frameborder = $values->frameborder;
            $row->align = $values->align;
            $row->allowabletags = $values->allowabletags;
            $row->maxitems = $values->maxitems;
            $row->truncate = $values->truncate;
            if (!is_16()) 
                $row->storage="content";  //default storage plugin. 
            else
                $row->storage="content16"; //default if is joomla 1.6 or bigger. 
        }
        $this->assignRef('row', $row);
        $list=getListsGen($row);
        $this->assignRef('lists',$list);
        $editor = &JFactory::getEditor();
        $this->assignRef('editor', $editor);
        $this->assignRef('option',$option);
        parent::display($tpl);
    }
}
