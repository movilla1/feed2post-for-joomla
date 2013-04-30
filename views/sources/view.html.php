<?php
/**
 * Feed2post v3.0
 * @author Mario O. Villarroel
 * @copyright 2010 - Elcan Software
 * @license GPLv2
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS."helper.php");
class Feed2PostViewSources extends JView {
    function display($tpl = null)
    {
        $cid = JRequest::getVar('cid', array(), '', 'array');
        $id = $cid[0]; //for now, only show the first item from the selected feed items list...
        $row =& JTable::getInstance("Feed2post");
        $row->load($id); //load feed data
        if (is_16()) {
            $option=JRequest::getCmd('option');
        } else {
            global $option;
        }
        $config = getConfig();
        $parserName=basename($row->parser,".parser.php");
        require_once(JPATH_COMPONENT_ADMINISTRATOR.DS."parsers".DS.$parserName.".parser.php");
        $parserClass=$parserName."F2pParser";
        $opts=F2pParser::getParserOptions($parserName);
        $parser=new $parserClass();
        $parser->setOptions($opts);
        $items=$parser->getItems($row,$config);
        $title=$parser->GetTitle();
        $this->assignRef('items',$items);
        $this->assignRef('title',$title);
        $this->assignRef('feed_url',$row->feed_url);
        $this->assignRef('id',$row->id);
        $this->assignRef('frontpage',$row->frontpage);
        $this->assignRef('option',$option);
        parent::display($tpl);
    }
}