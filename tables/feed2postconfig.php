<?php
/**
 * Feed2post v3.0
 * @author Mario O. Villarroel
 * @copyright 2010 - Elcan Software
 * @license GPLv2
 */
defined ('_JEXEC') or die('Restricted Access');
class JTableFeed2postConfig extends JTable {
  var $id=null;
  var $name=null;
  var $values=null;
  
  function __construct(&$db) {
    parent::__construct('#__feed2post_config','id',$db);
  }
}
?>
