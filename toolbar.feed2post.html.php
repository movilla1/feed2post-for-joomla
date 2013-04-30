<?php
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_COMPONENT_ADMINISTRATOR.DS."helper.php");

class TOOLBAR_feed2post
{
    var $version;
    function TOOLBAR_feed2post()
    {
        $this->version = JText::_('Feed2post v3.0rc10');
    }
    
    function _EDIT()
    {
        JToolBarHelper::title($this->version, 'generic.png');
        JToolBarHelper::save();
        JToolBarHelper::cancel();
    }

    function _DEFAULT()
    {
        JToolBarHelper::title($this->version, 'generic.png');
        JToolBarHelper::publish();
        JToolBarHelper::unpublish();
        JToolBarHelper::spacer();
        JToolBarHelper::divider();
        JToolBarHelper::spacer();
        JToolBarHelper::addNew();
        JToolBarHelper::editList();
        JToolBarHelper::deleteList();
        JToolBarHelper::spacer();
        JToolBarHelper::divider();
        JToolBarHelper::spacer();
        JToolBarHelper::custom('showSource', 'preview', 'preview', 'Items', true);
        JToolBarHelper::custom('postAll', 'apply', 'apply', 'Post All', false);
        /*Modificado por carolina para probar*/
        JToolBarHelper::custom('postSelected', 'upload', 'upload', 'Post Selected', true);
        JToolBarHelper::spacer();
        JToolBarHelper::divider();
        JToolBarHelper::spacer();
        JToolBarHelper::custom("cpanel", "back", "back", "Back", false);
    }
    
    function _SHOWFEED()
    {
        JToolBarHelper::title($this->version, 'generic.png');
        JToolBarHelper::custom('saveItem', 'save', 'save', 'Post', false);
        JToolBarHelper::cancel();
    }
    
    function _SAVEI()
    {
        JToolBarHelper::title($this->version, 'generic.png');
        if (!is_16()){
            JToolBarHelper::custom('showImport', 'preview', 'preview', 'Import Feeds', false);
        }
        JToolBarHelper::custom('saveOptions', 'save', 'save', 'Save', false);
        JToolBarHelper::cancel();
    }

    function _SAVEIMPORT()
    {
        JToolBarHelper::title($this->version, 'generic.png');
        if (!is_16()) // only allow saving to J1.5, function not available for 1.6 and higher. 
            JToolBarHelper::custom('saveImportedItems', 'save', 'save', 'Save', false);
        JToolBarHelper::cancel();
    }
    
    function _CPANEL()
    {
        JToolBarHelper::title($this->version, 'generic.png');
        JToolBarHelper::custom('itemList', 'preview', 'preview', 'View All Sources', false);
        JToolBarHelper::custom('showOptions', 'options', 'options', 'Feed2post Config', false);
        JToolBarHelper::custom('help', 'help', 'help', 'Help', false);
    }
    
    function _CANCEL()
    {
        JToolBarHelper::title($this->version, 'generic.png');
        JToolBarHelper::cancel();
    }
    
    function _SETDEFAULTS()
    {
        JToolBarHelper::title($this->version, 'generic.png');
        JToolBarHelper::custom('storeDefaults', 'save', 'save', 'Save', false);
        JToolBarHelper::cancel();
    }
}
?>