<?php
/**
 * Feed2post v3.0
 * @author Mario O. Villarroel
 * @copyright 2010 - Elcan Software
 * @license GPLv2
 * 
 * Engine to store the data gathered using Mighty Resources content items.
 */
defined('_JEXEC') or die('Restricted Access');
require_once (JPATH_COMPONENT_ADMINISTRATOR . DS . "engines" . DS .
    "F2pEngine.php");
class resourcesF2pEng extends F2pEngine
{
    function store($row, $config, $item)
    {
        return false;
    }
    
    function showEngineSettings($row)
    {
        return "<b>Work in progress</b>";
    }
    
    function checkGuid($guid, $sec_avoid = false, $opts)
    {
        return false;
    }    
}

?>