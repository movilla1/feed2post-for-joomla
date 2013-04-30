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
class Feed2PostViewOptions extends JView
{
    function display($tpl = null)
    {
        $mainframe=&JFactory::getApplication();
        if (is_16()) {
            $option=JRequest::getCmd('option');
        } else 
            global $option;
        $lists = array();
        $row =& JTable::getInstance("Feed2postConfig");
        $row->load('1'); //load config options
        $values = json_decode(stripslashes($row->values), true);
        /*$lists['getwith'] = JHTML::_('select.booleanlist', 'getwith', 'class="inputbox"',
            $values['getwith']);*/
        $lists['insertadvert'] = JHTML::_('select.booleanlist', 'insertadvert',
            'class="inputbox"', $values['insertadvert']);
        $lists['includelink'] = JHTML::_('select.booleanlist', 'includelink',
            'class="inputbox"', $values['includelink']);
        $dups[] = JHTML::_('select.option', '3', JText::_('No_duplicates_DB'),
            'id', 'title');
        $dups[] = JHTML::_('select.option', '128', JText::_('No_duplicates_section'),
            'id', 'title');
        $dups[] = JHTML::_('select.option', '254', JText::_('Allow Duplicates'), 'id',
            'title');
        $lists['avoiddup'] = JHTML::_('select.genericlist', $dups, 'dupavoid',
            'class="inputbox" size="1"', 'id', 'title', $values['dupavoid']);
        $lists['imagefolder']="<input type=\"text\" name=\"imagefolder\" id=\"imagefolder\" value=\"$values[imagefolder]\" size=\"50\"/>";
        $retries[]=JHTML::_('select.option', '1', "1 ".JText::_('TIME'), 'id','title');
        $retries[]=JHTML::_('select.option', '2', "2 ".JText::_('TIMES'), 'id','title');
        $retries[]=JHTML::_('select.option', '3', "3 ".JText::_('TIMES'), 'id','title');
        $retries[]=JHTML::_('select.option', '4', "4 ".JText::_('TIMES'), 'id','title');
        $retries[]=JHTML::_('select.option', '5', "5 ".JText::_('TIMES'), 'id','title');
        $retries[]=JHTML::_('select.option', '6', "6 ".JText::_('TIMES'), 'id','title');
        $retries[]=JHTML::_('select.option', '7', "7 ".JText::_('TIMES'), 'id','title');
        $lists['imageretries']=JHTML::_('select.genericlist',$retries,'imageretries', 'class="inputbox" size="1"','id','title',$values['imageretries']);    
        //$lists[] = $this->getList(false); old, not needed anymore...
        $plugins = $this->getPluginsList();
        $db =& JFactory::getDBO();
        foreach ($plugins as $plugin) {
            $plg=$db->getEscaped($plugin);
            $db->setQuery("SELECT * FROM #__feed2post_config WHERE name='$plg'");
            $opts[$plugin]=$db->loadAssoc();
        }
        $this->assignRef("opts",$opts);
        $this->assignRef('option',$option);
        $this->assignRef("plugins",$plugins);
        $this->assignRef("lists",$lists);
        $this->assignRef("badwords",$values['badwords']);
        parent::display();
    }
    
    function getPluginsList() {
        $df = opendir(JPATH_COMPONENT_ADMINISTRATOR.DS."parsers");
        if ($df) {
            while ($dat = readdir($df)) {
                if (strstr($dat, ".parser.php") !== false) {
                    $parser[] = basename($dat,".parser.php");
                }
            }
        } else {
            $parser[] = 'NO_PARSER';
        }    
        return $parser;
    }
}
