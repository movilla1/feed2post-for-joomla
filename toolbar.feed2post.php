<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
require_once( JApplicationHelper::getPath( 'toolbar_html' ) );
$tbfp=new TOOLBAR_feed2post();
switch($task)
{
  case 'new':
  case 'edit':
  case 'add':
  case 'remove':
  case 'publish':
  //case 'showSource':
	$tbfp->_EDIT();
	break;
  case 'showSource':
  	$tbfp->_SHOWFEED();
  	break;
  case 'showOptions':
  	$tbfp->_SAVEI();
  	break;
  case 'showImport':
	$tbfp->_SAVEIMPORT();
	break;
  case 'itemList':
    $tbfp->_DEFAULT();
    break;
  case 'showDefaults':
    $tbfp->_SETDEFAULTS();
    break;
  default:
	$tbfp->_CPANEL();
	break;

}
?>