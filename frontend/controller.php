<?php
	/**
     * Feed2post v3.0
     * @author Mario O. Villarroel
     * @copyright 2010 - Elcan Software
     * @license GPLv2
     * 
     * Main controller for the component's front-end side.
     */
defined( '_JEXEC' ) or die( 'Restricted Access' );
jimport('joomla.application.component.controller');
//require_once(JPATH_COMPONENT_ADMINISTRATOR.DS."controller.php");
class Feed2postSController extends JController {
    var $side="client";
    function iframe() {
        JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'tables');
        $iid=JRequest::getVar("item",0);
        $link=base64_decode(rawurldecode(JRequest::getVar("link","")));
        if ($iid!=0) {
            $row =& JTable::getInstance('Feed2post');
            $row->load($iid);
        } else {
            return false;
        }
        $iframeopts="marginheight='$row->marginheight' marginwidth='$row->marginwidth' frameBorder='$row->frameborder' scrolling='$row->scrolling' ";
        $iframeopts.="frameborder='$row->frameborder' align='$row->align' width='$row->width' height='$row->height'";
?>
		  
 <iframe src="<?php echo $link?>" <?php echo $iframeopts ?>></iframe><br style="clear: both;"/>
 <p>Powered by <a href='http://www.feed2post.com'>Feed2Post</a></p>   			  
<?php       
        return true;
    }
    
    function getImage() {
        require(JPATH_COMPONENT_ADMINISTRATOR.DS."defines.php");
        require_once(JPATH_COMPONENT_ADMINISTRATOR.DS."parsers".DS."F2pParsers.php");
        JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'tables');
        $imgid=JRequest::getVar("url",null);
        //$imgparts=split("#",$url);
        //$item=JRequest::getVar("item",0);
        if ($imgid!=null) {
            //get the item from the db to see the images it has and store them or get them from our server.
            if (file_exists(F2PIMAGEPATH.DS.$imgid)) {
                $imgdata=file_get_contents(F2PIMAGEPATH.DS.$imgid);
            } else {
                //get the file from the url stored.
                $db=JFactory::getDbo();
                $db->setQuery("SELECT * FROM #__feed2post_queue WHERE MD5(url) LIKE '".$db->getEscaped($imgid)."'");
                $db->query();
                $row=$db->loadAssoc();
                $imgdata=F2pParser::fetchWithCurl($row['url']);
                //print_r($imgdata);
                //exit();
                file_put_contents(F2PIMAGEPATH.DS.$imgid,$imgdata);
            }
            $finfo = getimagesize(F2PIMAGEPATH.DS.$imgid);
            $document = &JFactory::getDocument();
            $document->setMimeEncoding($finfo['mime']);
            //header ("Content-Type: ".$finfo['mime'] . "\n");
            echo $imgdata;
        } else {
            return false;
        }
        return true;
    }
}
?>