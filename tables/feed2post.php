<?php
/**
 * Feed2post v3.0
 * @author Mario O. Villarroel
 * @copyright 2010 - Elcan Software
 * @license GPLv2
 */
defined ('_JEXEC') or die('Restricted Access');
class JTableFeed2post extends JTable {
  var $id=null;
  var $feed_url=null;
  var $keywords=null;
  var $published=null;
  var $checked_out=null;
  var $checked_out_time=null;
  var $advert=null;
  var $fulltext=null;
  var $title=null;
  var $iframelinks=null;
  var $minkeylen=null;
  var $keycount=null;
  var $negkey=null;
  var $username=null;
  var $password=null;
  var $cutat=null;
  var $cutatcharacter=null;
  var $ignoreitem=null;
  var $minimum_count=null;
  var $height =null;
  var $width=null;
  var $marginheight=null;
  var $marginwidth=null;
  var $scrolling=null;
  var $frameborder=null;
  var $align=null;
  var $allowabletags=null;
  var $iframeclass=null;  
  var $parser=null;
  var $storage=null;
  var $storeoptions=null;
  var $parseroptions=null;
  var $includelink=null;
  var $replaceimgs=null;
  var $truncate=null;
      
  function __construct(&$db) {
    parent::__construct('#__feed2post','id',$db);
  }
}