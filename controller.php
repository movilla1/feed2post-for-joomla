<?php
/**
 * Feed2post v3.0
 * @author Mario O. Villarroel
 * @copyright 2010 - Elcan Software
 * @license GPLv2
 * 
 * Main controller for the component's admin side.
 */
defined('_JEXEC') or die('Restricted Access');
require(JPATH_COMPONENT_ADMINISTRATOR.DS."defines.php");
jimport('joomla.application.component.controller');
JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . DS . 'tables');
require_once (JPATH_COMPONENT_ADMINISTRATOR . DS . 'helper.php');

class Feed2postController extends JController
{
    var $side = "admin";
    /**
     * Shows the control pannel, should be the default when task=''
     */
    function display($cachable = false, $urlparams = false)
    {
        include JPATH_COMPONENT.DS.'views'.DS.'pannel'.DS.'view.html.php';
        $view = new Feed2PostViewPannel();
        $view->display();

    }

    /**
     * Default function to show the sources
     */
    function itemlist()
    {
        //JRequest::setVar('view', 'items');
        require_once JPATH_COMPONENT.DS.'views'.DS.'items'.DS.'view.html.php';
        $view = new Feed2PostViewItems();
        $view->display();
    }

    /**
     * Function to edit or add a new feed source
     */
    function edit()
    {
        //JRequest::setVar('view', 'single');
        require_once JPATH_COMPONENT.DS.'views'.DS.'single'.DS.'view.html.php';
        $view = new Feed2PostViewSingle();
        $view->display();
    }
    function add() {
        $this->edit();
    }

    function showDefaults()
    {
        require_once JPATH_COMPONENT.DS.'views'.DS.'defaults'.DS.'view.html.php';
        $view = new Feed2PostViewDefaults();
        $view->display();
        /*Feed2PostView::showDefaults($lists[0], $values->advert, $values->width, $values->
            height, $values->marginwidth, $values->marginheight, $option);*/
    }

    /**
     * Stores the defaults for each setting as selected by the user
     */
    function storeDefaults($option)
    {
        $mainframe =& JFactory::getApplication();
        $advert = JRequest::getVar('advert', '', 'post', 'string', JREQUEST_ALLOWRAW);
        $autounpub = JRequest::getVar('autounpublish', '0', 'post', 'int');
        $validfor = JRequest::getVar('validfor', '0', 'post', 'int');
        $frontpage = JRequest::getVar('frontpage', '0', 'post', 'int');
        $fulltext = JRequest::getVar('fulltext', '0', 'post', 'int');
        $posterid = JRequest::getVar('posterid', '0', 'post', 'int');
        $origdate = JRequest::getVar('origdate', '0', 'post', 'int');
        $iframelinks = JRequest::getVar('iframelinks', '0', 'post', 'int');
        $createddate = JRequest::getVar('createddate', '0', 'post', 'int');
        $showintro = JRequest::getVar('showintro', '0', 'post', 'int');
        $origauthor = JRequest::getVar('origauthor', '0', 'post', 'int');
        $acgroup = JRequest::getVar('acgroup', '0', 'post', 'int');
        $published = JRequest::getVar('published', '0', 'post', 'int');
        $intrometa = JRequest::getVar('intrometa', '0', 'post', 'int');
        $ignoreitem = JRequest::getVar('ignoreitem', '0', 'post', 'int');
        $height = JRequest::getVar('height', '', 'post', 'string', JREQUEST_ALLOWRAW);
        $width = JRequest::getVar('width', '', 'post', 'string', JREQUEST_ALLOWRAW);
        $marginheight = JRequest::getVar('marginheight', '0', 'post', 'int');
        $marginwidth = JRequest::getVar('marginwidth', '0', 'post', 'int');
        $scrolling = JRequest::getVar('scrolling', '', 'post', 'string',
            JREQUEST_ALLOWRAW);
        $frameborder = JRequest::getVar('frameborder', '0', 'post', 'int');
        $align = JRequest::getVar('align', '', 'post', 'string', JREQUEST_ALLOWRAW);
        $allowabletags = JRequest::getVar('allowabletags', '', 'post', 'string',
            JREQUEST_ALLOWRAW);
        $linkback=JRequest::getVar("linkback",'0','post','int');
        $maxitems=JRequest::getVar("maxitems",'0','post','int');    
        $values = array("validfor" => $validfor, "advert" => $advert, "autounpublish" =>
            $autounpub, "validfor" => $validfor, "frontpage" => $frontpage, "fulltext" => $fulltext,
            "posterid" => $posterid, "origdate" => $origdate, "iframelinks" => $iframelinks,
            "createddate" => $createddate, "showintro" => $showintro, "origauthor" => $origauthor,
            "acgroup" => $acgroup, "published" => $published, "sectionid" => 0, "catid" => 0,
            "intrometa" => $intrometa, "ignoreitem" => $ignoreitem, "height" => $height,
            "width" => $width, "marginheight" => $marginheight, "marginwidth" => $marginwidth,
            "scrolling" => $scrolling, "frameborder" => $frameborder, "align" => $align,
            "allowabletags" => $allowabletags,"includelink"=>$linkback,"maxitems"=>$maxitems
            );

        $db = &JFactory::getDBO();
        $values2 = $db->getEscaped(json_encode($values));
        $db->setQuery("UPDATE #__feed2post_config SET `values`='$values2' WHERE id='2'");
        $db->query();
        $mainframe->redirect("index.php?option=com_feed2post" , "Defaults stored<br/>\n" . $db->getErrorMsg());
    }
    
    function showOptions()
    {
        require_once JPATH_COMPONENT.DS.'views'.DS.'options'.DS.'view.html.php';
        $view = new Feed2PostViewOptions();
        $view->display();
    }

    function saveOptions()
    {
        $mainframe =& JFactory::getApplication();
        
        if (is_16()) {
            $option=JRequest::getCmd('option');
        } else {
            global $option;
        }
        JRequest::checkToken() or die('Invalid Token');
        // Get a database object
        $db = &JFactory::getDBO();
        $row = &JTable::getInstance('Feed2postConfig');
        $posted = JRequest::get('post');
        unset($posted['option']);
        unset($posted['task']);
        unset($posted['mask']);
        array_pop($posted);
        $values = $db->getEscaped(json_encode($posted));
        $row->load('1');
        $from = array("id" => '1', "name" => "basicsettings", "values" => "$values");
        if (!$row->bind($from)) {
            echo "<script> alert('" . $row->getError() .
                "'); window.history.go(-1); </script>\n";
            exit();
        }
        if (!$row->store()) {
            echo "<script> alert('" . $row->getError() .
                "'); window.history.go(-1); </script>\n";
            exit();
        }
        $row->checkin();
        $pluginOptions=JRequest::getVar('parseroption',array());
        if (count($pluginOptions)>0) {
            foreach ($pluginOptions as $k=>$options) {
                $nam=split("-",$k);
                $arr[$nam[0]][$nam[1]]=$options;
            }
            foreach ($arr as $plg=>$valus) {
                $valu=$db->getEscaped(json_encode($valus));
                $queryins="INSERT INTO #__feed2post_config VALUES ('','".$db->getEscaped($plg)."','".$valu."') ON DUPLICATE KEY UPDATE `values`='".$valu."'";
                $db->setQuery($queryins);
                $db->query();
            }
        }
        $mainframe->redirect('index.php?option=' . $option, 'Feed2post Options Saved');
    }

    function remove()
    {
        $mainframe=&JFactory::getApplication();
        if (is_16()) {
            $option=JRequest::getCmd('option');
        } else {
            global $option;
        }
        $cid = JRequest::getVar('cid', array(), '', 'array');
        $db = &JFactory::getDBO();
        if (count($cid)) {
            $cids = implode(',', $cid);
            $query = "DELETE FROM #__feed2post WHERE id IN ( $cids )";
            $db->setQuery($query);
            if (!$db->query()) {
                echo "<script> alert('" . $db->getErrorMsg() .
                    "');window.history.go(-1); </script>\n";
            }
        }
        $mainframe->redirect('index.php?option=' . $option . "&task=itemList","Item Removed");
    }
    /**
     * Saves the data entered for the source by the user
     */
    function save()
    {
        $mainframe=&JFactory::getApplication();
        if (is_16()){
            $option=JRequest::getCmd('option');
        } else {
            global $option;
        }
        JRequest::checkToken() or die('Invalid Token');
        $row = &JTable::getInstance('Feed2post');
        if (!$row->bind(JRequest::get('post'))) {
            echo "<script> alert('" . $row->getError() .
                "'); window.history.go(-1); </script>\n";
            exit();
        }
        $row->username=JRequest::getVar('u_field', "", 'post', 'string');
        $row->password=JRequest::getVar('p_field', "", 'post', 'string');
        
        $selections = JRequest::getVar('allowabletags', array(), 'post', 'array');
        $selectOption = JRequest::getVar('tags', '', 'post', 'string', JREQUEST_ALLOWRAW);
        $storeopts=JRequest::getVar('storeoptions',array(),"post",'array',JREQUEST_ALLOWRAW);
        $row->storeoptions=rawurlencode(json_encode($storeopts));
        
        if (empty($selections)) {
            $row->allowabletags = $selectOption;
        } else {
            $row->allowabletags = implode(" ", $selections);
        }

        $row->title = (strlen(trim($row->title)) < 2) ? JText::_("NO_TITLE") : trim($row->
            title);

        $row->advert = JRequest::getVar('advert', '', 'post', 'string',
            JREQUEST_ALLOWRAW);
        $row->cutat = JRequest::getVar('cutat', '', 'post', 'string', JREQUEST_ALLOWRAW);
        $row->cutatcharacter = JRequest::getVar('cutatcharacter', '', 'post', 'integer',
            JREQUEST_ALLOWRAW);
        if (!$row->store()) {
            echo "<script> alert('" . $row->getError() .
                "'); window.history.go(-1); </script>\n";
            exit();
        }
        $row->checkin();
        $mainframe->redirect('index.php?option=' . $option . "&task=itemList",
            'Feed2post Source Item Saved');
    }

    /**
     * Function to be called by the cancel button on the toolbar
     */
    function cancel()
    {
        $mainframe=&JFactory::getApplication();
        if (is_16()) {
          $option=JRequest::getCmd('option');   
        } else {
           global $option; 
        }

        // Check for request forgeries
        JRequest::checkToken() or die('Invalid Token');

        // Check the article in if checked out
        $row = &JTable::getInstance('Feed2post');
        $row->bind(JRequest::get('post'));
        $row->checkin();

        $mainframe->redirect('index.php?option='.$option.'&task=itemList');
    }
    
    function publish() {
        $option = JRequest::getCmd('option');
        $cid = JRequest::getVar('cid', array(), '', 'array');
        $this->changeState($option,$cid,2);
    }
    
    function unpublish() {
        $option = JRequest::getCmd('option');
        $cid = JRequest::getVar('cid', array(), '', 'array');
        $this->changeState($option,$cid,0);        
    }
    /**
     * Changes the state of a source, published or unpublished.
     */
    function changeState($option, $cid, $value)
    {
        $mainframe=&JFactory::getApplication();
        $db = &JFactory::getDBO();
        $ids = implode(",", $cid);
        $queryUpd = "UPDATE #__feed2post SET published='" . mysql_escape_string($value)."' WHERE id IN ($ids)";
        //$db->setQuery();
        $db->execute($queryUpd);
        if ($db->getErrorNum()) {
            $msg = JText::_("ERROR_UPDATING_ITEM");
            $msg .= $db->stderr();
        } else {
            $stat = ($value == 0) ? JText::_("Unpublished") : JText::_("Published");
            $msg = JText::_("Items") . " " . $ids . " " . JText::_("ARE_NOW") . " " . $stat;
        }
        $mainframe->redirect('index.php?option=com_feed2post', $msg);
    }

    function showHelp()
    {
        $mainframe=& JFactory::getApplication();
        $mainframe->redirect('http://www.feed2post.com/support/forum');
    }
    
    function showImport()
    {
        $mainframe=&JFactory::getApplication();
        if (is_16()) {
            $option=JRequest::getCmd('option');
        } else {
            global $option;
        }
        /*HTML_feedpost::showImports($rows, $lists[0], $lists[1], $option);*/
        require_once JPATH_COMPONENT.DS.'views'.DS.'imports'.DS.'view.html.php';
        $view = new Feed2PostViewImports();
        $view->display();
    }

    function saveImportedItems()
    {
        $mainframe=JFactory::getApplication();
        if (is_16()){
            $option=JRequest::getCmd('option');
        } else {
            global $option;
        }
        require_once (JPATH_ADMINISTRATOR . DS . "components" . DS . "com_newsfeeds" .
            DS . "tables" . DS . "newsfeed.php");
        $cids = JRequest::getVar('cid', array(), 'post', 'array');
        $autounpub = JRequest::getVar('autounpublish', 0, 'post', 'int');
        $front = JRequest::getVar('frontpage', 0, 'post', 'int');
        $delay = JRequest::getVar('delay', 10, 'post', 'int');
        $fullt = JRequest::getVar('fulltext', 0, 'post', 'int');
        $validf = JRequest::getVar('validfor', 1, 'post', 'int');
        $sectid = JRequest::getVar('sectionid', 0, 'post', 'int');
        $catid = JRequest::getVar('catid', 0, 'post', 'int');
        $advert = JRequest::getVar('autounpublish', "", 'post', 'string',
            JREQUEST_ALLOWRAW);

        foreach ($cids as $cid) {
            $rownewsf = JTable::getInstance("NewsFeed");
            $rownewsf->load($cid);
            $row_feedpost = JTable::getInstance("Feed2post");
            $row_feedpost->feed_url = $rownewsf->link;
            $row_feedpost->sectionid = $sectid;
            $row_feedpost->catid = $catid;
            $row_feedpost->advert = $advert;
            $row_feedpost->fulltext = intval($fullt);
            $row_feedpost->frontpage = intval($front);
            $row_feedpost->validfor = intval($validf);
            $row_feedpost->delay = intval($delay);
            $row_feedpost->autounpublish = intval($autounpub);
            $row_feedpost->store();
        }
        $mainframe->redirect("index.php?option=" . $option,
            "All selected feeds where imported, please edit them and add the keywords you need");
    }
    
    function showSource() {
        //here should load the view and show the results.
        require_once(JPATH_COMPONENT.DS."views".DS."sources".DS."view.html.php");
        $srcView=new Feed2PostViewSources();  
        $srcView->display();  
    }
    
    //function to save the selected src items as output
    function saveItem() { 
      $cids = JRequest::getVar('cid', array(), 'post', 'array');
      $content = JRequest::getVar('contents', array(), 'post', 'array', JREQUEST_ALLOWRAW);
      $id = JRequest::getVar('id', '', 'post', 'int');
      JRequest::checkToken() or die('Invalid Token');
      $row_feed=& JTable::getInstance("Feed2post");
      $row_feed->load($id);
      $storename=basename($row_feed->storage,".engine.php");
      require_once(JPATH_COMPONENT_ADMINISTRATOR.DS."helper.php");
      require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'engines'.DS.$storename.".engine.php");
      $storeClass=$storename."F2pEng";
      //print_r($storeClass);
      $storeObj=new $storeClass();
      $configs_fp=getConfig();
      $opts=json_decode(rawurldecode($row_feed->storeoptions));
      if (is_array($content) && is_array($cids)) {
        foreach ($cids as $iden) {
            $item = json_decode(rawurldecode($content[$iden]));
            if ($configs_fp->dupavoid != 254) {
                $sec_avoid = ($configs_fp->dupavoid == 128);
                if ($storeObj->checkGuid($item->guid, $sec_avoid, $opts)) {
                    $storeObj->store($row_feed,$configs_fp,$item);
                }    
            } else {
                $storeObj->store($row_feed,$configs_fp,$item);
            }
        }
      } 
      $mainframe=&JFactory::getApplication();
      $mainframe->redirect("index.php?option=com_feed2post&task=itemList", "Items saved");
    }
    
    function postAll() {
        //store the values from all the sources
        
        $segment=JRequest::getVar('seg',false);
        $noreturn=JRequest::getVar('nr',0);
        $queryFeeds = "SELECT * FROM #__feed2post WHERE published='1'";
        $configs_fp = getConfig();
        if ($segment !== false) {
            $queryFeeds .= " LIMIT " . _ROWS_PER_SEG . "," . mysql_escape_string(intval($segment) *
                _ROWS_PER_SEG);
        }
        $db = &JFactory::getDBO();
        $db->setQuery($queryFeeds);
        $feeds = $db->loadObjectList();
        if ($db->getErrorNum() <> 0) {
            $msgout = "Error:" . $db->stderr();
            if (!isset($noreturn) || (isset($noreturn) && $noreturn != 1))
                $mainframe->redirect('index.php?option=' . $option, $msgout);
            else {
                echo $msgout;
                return false;
            }
        }    
        foreach ($feeds as $feed) {
            $parserName=basename($feed->parser,".parser.php");
            $storename=basename($feed->storage,".engine.php");
            require_once(JPATH_COMPONENT_ADMINISTRATOR.DS."parsers".DS.$parserName.".parser.php");
            require_once(JPATH_COMPONENT_ADMINISTRATOR.DS."engines".DS.$storename.".engine.php");
            $parserClass=$parserName."F2pParser";
            $parser=new $parserClass();
            $items=$parser->getItems($feed,$configs_fp);
            $msgout .= "Processing: " . $feed->title . "\n";
            $opts=json_decode(rawurldecode($feed->storeoptions));
            $storeClass=$storename."F2pEng";
            $storeObj=new $storeClass();
            $procc=0;
            foreach ($items as $k => $val) {
                $item = json_decode(rawurldecode($val));
                if ($configs_fp->dupavoid != 254) {
                    $sec_avoid = ($configs_fp->dupavoid == 128);
                    if ($storeObj->checkGuid($item->guid, $sec_avoid, $opts)) {
                        $storeObj->store($feed,$configs_fp,$item);
                    }    
                } else {
                    $storeObj->store($feed,$configs_fp,$item);
                }
                $procc++;
            }
            $msgout.=", Processed $procc items<br/>\n";
        }
        if (!isset($noreturn) || (isset($noreturn) && $noreturn != 1)) {
            $mainframe=& JFactory::getApplication();
            $mainframe->redirect('index.php?option=com_feed2post&task=itemList',$msgout);
        } else {
            echo $msgout;
        }
    }
    
    function postSelected() {
        $cids = JRequest::getVar('cid', array(), '', 'array');
        $configs_fp=getConfig();
        foreach ($cids as $id) {
        //$id=$cid[0]; //for now, only show the first item from the selected feed items list...
            $feed =& JTable::getInstance("Feed2post");
            $feed->load($id); //load feed data
            $parserName=basename($feed->parser,".parser.php");
            $storename=basename($feed->storage,".engine.php");
            require_once(JPATH_COMPONENT_ADMINISTRATOR.DS."parsers".DS.$parserName.".parser.php");
            require_once(JPATH_COMPONENT_ADMINISTRATOR.DS."engines".DS.$storename.".engine.php");
            $parserClass=$parserName."F2pParser";
            $parser=new $parserClass();
            $items=$parser->getItems($feed,$configs_fp);
            $msgout .= "Processing: " . $feed->title . "\n";
            $opts=json_decode(rawurldecode($feed->storeoptions));
            $storeClass=$storename."F2pEng";
            $storeObj=new $storeClass();
            foreach ($items as $k => $val) {
                $item = json_decode(rawurldecode($val));
                
                if ($configs_fp->dupavoid != 254) {
                    $sec_avoid = ($configs_fp->dupavoid == 128);
                    if ($storeObj->checkGuid($item->guid, $sec_avoid, $opts))
                        $storeObj->store($feed,$configs_fp,$item);
                } else {
                    $storeObj->store($feed,$configs_fp,$item);
                }
            }
        }
        if (!isset($noreturn) || (isset($noreturn) && $noreturn != 1)) {
            $mainframe=& JFactory::getApplication();
            $mainframe->redirect('index.php?option=com_feed2post&task=itemList',$msgout);
        } else {
            echo $msgout;
        }
    }
    
    /**
     * This task gets called from the panel ajax call
     */
    function getStoreOpts() {
        //ini_set("display_errors","1");
        $rid=JRequest::getVar("id",0);
        $engname=JRequest::getVar('eng',"content");
        $act=JRequest::getVar('act',0);
        if ($act<1) return false;
        $engine=basename($engname,".engine.php");
        require_once(JPATH_COMPONENT_ADMINISTRATOR.DS."engines".DS.$engine.".engine.php");
        $engineName=$engine."F2pEng";
        if (class_exists($engineName)) {
            $row=&JTable::getInstance("Feed2post");
            if ($rid>0) $row->load($rid);
            $cls=new $engineName();
            $data=$cls->showEngineSettings($row);
            echo $data;
        } else {
            echo "<b>Failed Engine</b>";
        }
    }
    
     // CNC - grab the images from the internet
	function grabImages() {
        // store the values from all the sources
		// Config Variables
		//  imageoutput = media/feed2post/images
		//  imageretries = 5
		// Table: _feed2post_queue (images)
		//  url
		//  done
        
        $segment=JRequest::getVar('seg',false);
        $noreturn=JRequest::getVar('nr',0);
        $queryFeeds = "SELECT * FROM #__feed2post_queue WHERE type='image'";
        if ($segment !== false) {
            $queryFeeds .= " LIMIT " . _ROWS_PER_SEG . "," . mysql_escape_string(intval($segment) *
                _ROWS_PER_SEG);
        }
        $db = &JFactory::getDBO();
        $db->setQuery($queryFeeds);
        $images = $db->loadObjectList();
        if ($db->getErrorNum() <> 0) {
            $msgout = "Error:" . $db->stderr();
            if (!isset($noreturn) || (isset($noreturn) && $noreturn != 1))
                $mainframe->redirect('index.php?option=' . $option, $msgout);
            else {
                echo $msgout;
                return false;
            }
        }
		$config_fp=getConfig();
        
		$imageoutput = JPATH_BASE.DS.$config_fp->imageoutput;
        if (file_exists($imageoutput)!=true) {
            $msgout=JText::_("FOLDERNOTFOUND");
            $mainframe->redirect("index.php?option=com_feed2post",$msgout);
        }
		$imageretries = $config_fp->imageretries;
        $procc=0;
        foreach ($images as $image) {		
			$msgout .= JText::_("DOWNLOADING")." :" . $image->url . "<br/>";
			$kill_row = false;
			$update_counter = false;
			// grab the image from the internet and store it in the output directory
			$in =   fopen( $image->url, "rb");			
			// Can we open?  
			if( $in == NULL) {
				// Increment counter on failed attempts to open
				$image->done += 1;
				$msgout .= " - ". JText::_("TRIED") . $image->done . " times -";
				if( $image->done >= $imageretries) { 
					$kill_row = true;
					$msgout .= JText::_("GIVINGUP");
				} else {
					$update_counter = true;
					$msgout .= JText::_("WILLTRYAGAIN");
				}
			} else {
				$file_name = JPATH_BASE .DS. $imageoutput .DS. basename($image->url);
				$out =  fopen( $file_name, "wb");
				if( $out == NULL) {
				  $msgout .= " - ".JText::_("cannotwrite").": " . $file_name . "!";
				} else {
					while ($chunk = fread( $in,8192)) {
						fwrite($out, $chunk, 8192);
					}
					$kill_row = true;
					fclose($out);
					$msgout .= " - ".JText::_("IMAGESAVEDTO").": " . $file_name . "!";
				}
				fclose($in);
			}
			
			if( $kill_row) {
				// ToDo Remove the row from the database
				$queryUpdel = "DELETE FROM #__feed2post_queue WHERE id=" . $image->id;
			} elseif( $update_counter) {
				$queryUpdel = "UPDATE #__feed2post_queue SET done=" . $image->done . " WHERE id=" . $image->id;
			}
			if( $queryUpdel) {
				$db->setQuery($queryUpdel);
				$update = $db->loadObjectList();
			}
			
			$msgout .= "<br/>\n";
			++$procc;
		}
		$msgout .="Processed $procc images<br/>\n";
		
        if (!isset($noreturn) || (isset($noreturn) && $noreturn != 1)) {
            $mainframe=& JFactory::getApplication();
            $mainframe->redirect('index.php?option=com_feed2post&task=listAll',$msgout);
        } else {
            echo $msgout;
        }
    }
    
/*    function grabExtraContent() {
                $segment=JRequest::getVar('seg',false);
        $noreturn=JRequest::getVar('nr',0);
        $queryFeeds = "SELECT * FROM #__feed2post_queue WHERE type='content'";
        if ($segment !== false) {
            $queryFeeds .= " LIMIT " . _ROWS_PER_SEG . "," . mysql_escape_string(intval($segment) *
                _ROWS_PER_SEG);
        }
        $db = &JFactory::getDBO();
        $db->setQuery($queryFeeds);
        $extras = $db->loadObjectList();
        if ($db->getErrorNum() <> 0) {
            $msgout = "Error:" . $db->stderr();
            if (!isset($noreturn) || (isset($noreturn) && $noreturn != 1))
                $mainframe->redirect('index.php?option=' . $option, $msgout);
            else {
                echo $msgout;
                return false;
            }
        }
		$config_fp=getConfig();
        foreach ($extras as $extra) {
            echo $extra->url;
        }
    }*/
}
?>