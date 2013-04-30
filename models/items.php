<?php
/**
 * Feed2post v3.0
 * @author Mario O. Villarroel
 * @copyright 2010 - Elcan Software
 * @license GPLv2
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.model');
class Feed2postModelItems extends JModel
{
    var $data = null;
    var $sort = null;
    var $start = null;
    var $limit = null;
    function getData()
    {
        $db = &JFactory::getDBO();
        $doc = &JFactory::getDocument();
        if (empty($this->data)) {
            $querycount = "SELECT COUNT(*) FROM #__feed2post WHERE 1";
            $db->setQuery($querycount);
            $total = $db->loadResult();
            $query = "SELECT * FROM #__feed2post WHERE 1 order by ".$this->sort;
            $db->setQuery($query, $this->start, $this->limit);
            $this->data = $db->loadObjectList();
        }
        return $this->data;
    }

    function setOrder($sortby)
    {
        switch ($sortby) {
            case 1:
                $this->sort = "title";
                break;
            case 2:
                $this->sort = "validfor";
                break;
            case 3:
                $this->sort = "keywords";
                break;
            default:
                $this->sort = "id";
        }
    }
    
    function setLimits($start,$limit) {
        $this->limit=$limit;
        $this->start=$start;
    }
}
