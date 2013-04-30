<?php
/**
 * Feed2post v3.0
 * @author Mario O. Villarroel
 * @copyright 2010 - Elcan Software
 * @license GPLv2
 * 
 * Engine to store the data gathered using std. joomla content items.
 */
defined('_JEXEC') or die('Restricted Access');
require_once (JPATH_COMPONENT_ADMINISTRATOR . DS . "engines" . DS .
    "F2pEngine.php");
require_once (JPATH_COMPONENT_ADMINISTRATOR.DS."helper.php");
jimport("joomla.form.form");

class content16F2pEng extends F2pEngine
{    
    function store($row, $config, $item)
    {
        $opts=json_decode(rawurldecode($row->storeoptions));
        $uri =& JURI::getInstance();
        $configs_fp = $config;
        $db = &JFactory::getDBO();
        $user = &JFactory::getUser();
    
        $row_feed = $row;
        $nullDate = $db->getNullDate();
        $advertisement = html_entity_decode(urldecode(JRoute::_($row_feed->advert)));
        $siteurl=$uri->base();
        if (stripos($siteurl,"/administrator/") == (strlen($siteurl)-15)) {
             $siteurl=substr($siteurl,0,strlen($siteurl)-15)."/";
        }
        $link = urldecode($item->link);
        $title = html_entity_decode($item->title);
        $date = urldecode($item->date);
        $content = urldecode($item->content);
        $guid = urldecode($item->guid);
        $author = urldecode($item->author);
        $introText = "<div id='article_intro_f2p'>" . stripslashes(urldecode($item->description)) . "</div>";
        $metadesc = substr(strip_tags(rawurldecode($item->description)),0,180);  //Cut only 180 chars for SEO.
        $tmp="";
        foreach ($item->images as $imgurl) {
        	$tmp.="<div class='f2p_image'><img src='$imgurl' border='0' alt='Image'></div>";
        }
        $fullText = $content;
        $fullText .= ($opts->origdate == 1) ? "<br/>\n" . JText::_("Posted") . ": $date" : "";
        $linkbackText = (isset ($row_feed->linkbacktext) && strlen(trim($row_feed->linkbacktext))>2) ? trim($row_feed->linkbacktext) : JText::_("READ_FULL_ARTICLE");
        if ($row_feed->includelink == "1") {
            $site = $uri->root() . "/components/com_feed2post/feed2post.php";
            if ($row_feed->iframelinks == 1) {
                $llink=rawurlencode(base64_encode(urldecode($link)));
                $linkback="<div id='article_full_f2p'><a href='".JRoute::_($uri->root()."index.php?option=com_feed2post&item={$row->id}&task=iframe&link=$llink")."'>$linkbackText</a></div>\n";
            } else {
                $linkback = "<div id='article_full_f2p'><br/><a href='$link' target='_blank'>". $linkbackText . " </a></div>";
            }
        }

        jimport('joomla.utilities.date');

        // Initialize variables
        $row =& JTable::getInstance('content');
        $config =& JFactory::getConfig();
        $tzoffset = $config->getValue('config.offset');

        $tmpdate = new JDate($row->created, $tzoffset);
        $row->checkout($user->get('id'),$user->get('id').rand(0,9)); //checkit out by me.
        if ($opts->createddate == 1) {
            $date_cr = strtotime($date);
            if ($date_cr === -1) {
                $created = $tmpdate->toMySQL();
            } else {
                //$tmp=new JDate($date,$tzoffset);
                $created = $date;
            }
        } else {
            $created = $tmpdate->toMySQL();
        }
        $row->created = $created;
        $row->catid = $opts->catid;
        $row->version = 0;
        $row->state = 1;
        $row->ordering = 0;
        $row->images = array();
        $row->access = $opts->acgroup;
        $row->attribs = "{\"show_title\":\"\",\"link_titles\":\"\",\"show_intro\":\"". $opts->showintro ."\",\"show_category\":\"\",
        \"link_category\":\"\", \"show_parent_category\":\"\", \"link_parent_category\":\"\", \"show_author\":\"\",
        \"link_author\":\"\", \"show_create_date\":\"\", \"show_modify_date\":\"\", \"show_publish_date\":\"\",
        \"show_item_navigation\":\"\", \"show_icons\":\"\", \"show_print_icon\":\"\", \"show_email_icon\":\"\",
        \"show_vote\":\"\", \"show_hits\":\"\", \"show_noauth\":\"\", \"page_title\":\"\",
        \"alternative_readmore\":\"".$opts->alternativereadmore."\", \"keyref\":\"". $guid ."\", \"layout\":\"\"}";
        //Set the unpublish time
        if ($opts->autounpublish == 1) {
            $publish_down = time() + $opts->validfor;
            $date = new JDate($publish_down, $tzoffset);
            $row->publish_down = $date->toMySQL();
        } else
            $row->publish_down = $nullDate;
        //set the start publishing time.
        $publish_up = date("Y-m-d");
        $dateup = new JDate($publish_up, $tzoffset);
        $row->publish_up = $dateup->toMySQL();

        $row->created_by = $opts->posterid;
        if ($opts->origauthor == 1) {
            $row->created_by_alias = $author;
        }
        $row->modified = $nullDate;
        $row->modified_by = $opts->posterid;
        switch($opts->enclosed_img) {
	       	case 'head-top':
	       		$introText=$tmp.$introText;
	       		break;
	       	case 'head-bot':
	       		$introText.=$tmp;
	       		break;
	       	case 'body-top':
	       		$fullText=$tmp.$fullText;
	       		break;
	       	case 'body-bot':
	      		$fullText.=$tmp;
	       		break;
	    }
        
        $row->introtext = str_replace('<br>', '<br />', $introText);
        //Now check the fulltext area, if cutat has something, then cut the intro...
        if ($row_feed->fulltext == "1" || strlen(trim($row_feed->cutat)) > 0) {
            $row->fulltext = ($configs_fp->insertadvert == "1") ? $advertisement : "";
            $row->fulltext .= "<br/>\n" . str_replace('<br>', '<br />', $fullText);
            if ($opts->origauthor == 2) $row->fulltext .= JText::_("Author:") . $author;
            $row->fulltext .= ($configs_fp->includelink == "1") ? $linkback : "";
        } else { //No full text, all into introtext
            $row->introtext .= ($configs_fp->insertadvert == "1") ? $advertisement : "";
            if ($opts->origauthor == 2) $row->introtext .= JText::_("Author:") . $author;
            $row->introtext .= ($configs_fp->includelink == "1") ? $linkback : "";
        }
        if ($opts->intrometa == 1) $row->metadesc=$metadesc;
        $row->title_alias = $this->checkAlias(JRoute::_(html_entity_decode($title)));
        $row->title = JRoute::_(html_entity_decode($title, ENT_QUOTES), true); // SEO aware
        if (strlen(trim($row->title)) <= 0) {
            $row->title = $row_feed->title . " - " . Date("Y-m-d H:i:s");
        }
        $row->featured=$opts->frontpage;
        if (!$row->check()) {
            JError::raiseError(500, "Error Checking<br/>".$db->stderr());
            return false;
        }
        // Increment the content version number
        $row->version++;
        $row->language = $opts->language;
        // Store the content to the database
        if (!$row->store()) {
            JError::raiseError(501, "Error Storing<br/>".$row->getError());
            return false;
        }

        // Check the article and update item order
        $row->checkin();
        $row->reorder('catid = ' . (int)$row->catid . ' AND state >= 0');
        //This is extracted from com_content...
        /*
        * We need to update frontpage status for the article.
        *
        * First we include the frontpage table and instantiate an instance of it.
        */
        if (file_exists(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_content' . DS . 'tables' . DS . 'featured.php')) {
            require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_content' . DS . 'tables' . DS . 'featured.php');
        } else if (file_exists(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_content' . DS . 'tables' . DS . 'Featured.php')) {
            require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_content' . DS . 'tables' . DS . 'Featured.php');
        }
        $fp = new ContentTableFeatured($db);

        // Is the article viewable on the frontpage?
        if ($opts->frontpage==1) {
            // Is the item already viewable on the frontpage?
            if (!$fp->load($row->id)) {
                // Insert the new entry
                $query = 'INSERT INTO #__content_frontpage' . ' VALUES ( ' . (int)$row->id .
                    ', 1 )';
                $db->setQuery($query);
                if (!$db->query()) {
                    JError::raiseError(500, $db->stderr());
                    return false;
                }
                $fp->ordering = 1;
            }
        } else {
            // Delete the item from frontpage if it exists
            if (!$fp->delete($row->id)) {
                $msg .= $fp->stderr();
            }
            $fp->ordering = 0;
        }
        if ($opts->frontpage==1) $fp->reorder();
        return true;        
    }

    function showEngineSettings($row)
    {
        $list = $this->getLists($row);
        $lists=$list[0];
        $sectioncategories=$list[1];
        ob_start();
?>
<script language="javascript" type="text/javascript">
</script>
<table align="center" class="adminlist">
   <tr >
  <th style="width: 140px"><?php
        echo JHTML::tooltip(JText::_('Category_Tooltip'), JText::_("Category"), '',
            JText::_("Category"));
?>:</th>
  <td ><?php
        echo $lists['catid'];
?></td>
   <th style="width: 140px"><?php
        echo JHTML::tooltip(JText::_('Auto_Unpublish_Tooltip'), JText::_("Auto Unpublish"),
            '', JText::_("Auto Unpublish"));
?>:</th>
   <td><fieldset id="jform_type" class="radio inputbox"><?php echo $lists["autounpub"];?></fieldset></td>
  </tr>
  <tr>
   <th style="width: 140px" ><?php
        echo JHTML::tooltip(JText::_('VALID_FOR_TOOLTIP'), JText::_("VALID_FOR"), '',
            JText::_("VALID_FOR"));?>:</th>
   <td  colspan="1"><?php echo $lists['validfor']?></td>
   <th style="width: 140px"><?php
        echo JHTML::tooltip(JText::_('Include_Original_Date_Tooltip'), JText::_("Include Original Date"),
            '', JText::_("Include Original Date"));
?>:</th>
   <td ><fieldset id="jform_type" class="radio inputbox"><?php echo $lists['origdate']; ?></fieldset></td>
  </tr> 
  <tr >
   <th style="width: 140px"><?php
        echo JHTML::tooltip(JText::_('Set_feed_date_as_created_date_Tooltip'), JText::_
            ("Set feed date as created date"), '', JText::_("Set feed date as created date"));
?>*</th>
   <td><fieldset id="jform_type" class="radio inputbox"><?php echo $lists['createddate'];?></fieldset></td>
      <th style="width: 140px">
       <?php
        echo JHTML::tooltip(JText::_('Alternative_Read_More_Tooltip'), JText::_("Alternative Read More"),
            '', JText::_("Alternative Read More"));
?>:
      </th>
      <td><?php echo $lists['alternativereadmore']?> </td>
  </tr>
  <tr>
      <th style="width: 140px">
        <?php echo JHTML::tooltip(JText::_('introMeta_Tooltip'), JText::_("Intro_As_Meta"),'tooltip.png', JText::_('Intro_As_Meta'), '');?>
      </th>
      <td><fieldset id="jform_type" class="radio inputbox"><?php echo $lists['intrometa'] ?></fieldset></td>
     <th style="width: 140px"><?php
        echo JHTML::tooltip(JText::_('Post_as_user_Tooltip'), JText::_("Post as user"),
            '', JText::_("Post as user"));
?>:</th>
     <td><?php echo $lists['users'];?></td> 	
  </tr>
  <tr>
     <th style="width: 140px"><?php
        echo JHTML::tooltip(JText::_('Store_Author_Tooltip'), JText::_("Store Author"),
            '', JText::_("Store Author"));
?></th>
     <td><?php  echo $lists['origauthor'];?></td>
	 	<th style="width: 140px"><?php
        echo JHTML::tooltip(JText::_('Access_Group_Tooltip'), JText::_("Access Group"),
            '', JText::_("Access Group"));?>:</th>
  		<td><?php  echo $lists['accessgrp']; ?></td>
  </tr>
  <tr>
  		   <th style="width: 140px"><?php echo JHTML::tooltip(JText::_('Front_Page_Tooltip'), JText::_("Front Page"), '', JText::_("Front Page"));?>:</th>
   <td><fieldset id="jform_type" class="radio inputbox"><?php echo $lists['frontpage']; ?></fieldset></td>
    <th style="width: 140px"><?php echo JHTML::tooltip(JText::_('Show_intro_Tooltip'), JText::_("Show intro"), '', JText::_("Show intro"));?>:</th>
    <td><fieldset id="jform_type" class="radio inputbox"><?php echo $lists['showintro'];?></fieldset></td>
  </tr>
  <tr>
    <th style="width: 140px;"><?php echo JHTML::tooltip(JText::_('LANGUAGE_TOOLTIP'), JText::_("Language"), '', JText::_("Language"));?>:</th>
    <td><?php echo $lists['languages']?></td>
    <th style="width: 140px"><?php echo JHTML::tooltip(JText::_('ENCLOSED_IMAGES'),JText::_('TITLE_ENCLOSED_IMAGES'),'tooltip.png',JText::_('TITLE_ENCLOSED_IMAGES'))?></th>
    <td><?php echo $lists['enclosed_img'];?></td>
  </tr>
</table>
<?php
        $paneldata = ob_get_contents();
        ob_end_clean();
        return $paneldata;
    }

    function getLists($row)
    {
        $db = &JFactory::getDBO();
        $row_opt=json_decode(rawurldecode($row->storeoptions));
        $query = "SELECT username,id FROM #__users WHERE 1";
        $db->setQuery($query);
        $users[] = JHTML::_('select.option', '0', JText::_('Please select'), 'id',
            'username');
        $users = array_merge($users, $db->loadObjectList());
        $lists['users'] = JHTML::_('select.genericlist', $users, 'storeoptions[posterid]',
            'size="1" ', 'id', 'username', intval($row_opt->posterid));
        $form=new JForm("formcateg");
        //$form->addFormPath();
        $form->loadFile(JPATH_COMPONENT_ADMINISTRATOR.DS."engines".DS."formcateg.xml");
        $lists['catid']=$form->getInput('storeoptions[catid]',null,$row_opt->catid);
        $lists['autounpub'] = JHTML::_('select.booleanlist', 'storeoptions[autounpublish]',
            '', $row_opt->autounpublish);
        $lists['showintro'] = JHTML::_('select.booleanlist', 'storeoptions[showintro]',
            '', $row_opt->showintro);
        $lists['intrometa'] = JHTML::_('select.booleanlist', 'storeoptions[intrometa]',
            '', $row_opt->intrometa);
        $author[] = JHTML::_('select.option', '0', JText::_("Don't store"), 'id',
            'title');
        $author[] = JHTML::_('select.option', '1', JText::_('As article Auhtor alias'),
            'id', 'title');
        $author[] = JHTML::_('select.option', '2', JText::_('As Text'), 'id', 'title');
        $lists['origauthor'] = JHTML::_('select.genericlist', $author, 'storeoptions[origauthor]',
            'size="1"', "id", "title", $row_opt->origauthor);

        $lists['frontpage'] = JHTML::_('select.booleanlist', 'storeoptions[frontpage]', '', $row_opt->frontpage);
        $valids[] = JHTML::_('select.option', '-1', JText::_('Ever'), 'value', 'text');
        $valids[] = JHTML::_('select.option', '84200', JText::_('1 Day'), 'value',
            'text');
        $valids[] = JHTML::_('select.option', '604800', JText::_('7 Days'), 'value',
            'text');
        $valids[] = JHTML::_('select.option', '1209600', JText::_('14 Days'), 'value',
            'text');
        $valids[] = JHTML::_('select.option', '2592000', JText::_('30 Days'), 'value',
            'text');
        $valids[] = JHTML::_('select.option', '5184000', JText::_('60 Days'), 'value',
            'text');
        $valids[] = JHTML::_('select.option', '7776000', JText::_('90 Days'), 'value',
            'text');
        $valids[] = JHTML::_('select.option', '15552000', JText::_('180 Days'), 'value',
            'text');
        $valids[] = JHTML::_('select.option', '23328000', JText::_('270 Days'), 'value',
            'text');
        $valids[] = JHTML::_('select.option', '31536000', JText::_('365 Days'), 'value',
            'text');
        $lists['validfor'] = JHTML::_('select.genericlist', $valids, 'storeoptions[validfor]',
            '', 'value', 'text', intval($row_opt->validfor));
        $delays[] = "No Delay";
        $lists['delay'] = JHTML::_('select.genericlist', $delays, 'storeoptions[delay]',
            '', 'id', 'title', intval($row_opt->delay));
        $lists['origdate'] = JHTML::_('select.booleanlist', 'storeoptions[origdate]',
            '', $row_opt->origdate);
        $lists['createddate'] = JHTML::_('select.booleanlist', 'storeoptions[createddate]',
            '', $row_opt->createddate);
        
        $lists['accessgrp'] = $form->getInput("storeoptions[acgroup]",null,$row_opt->acgroup);
        $lists['alternativereadmore'] = "<input type=\"text\" value=\"".$row_opt->alternativereadmore."\" ";
        $lists['alternativereadmore'].="name=\"storeoptions[alternativereadmore]\" size=\"15\" maxlength=\"20\" />";
        $lists['languages']=$form->getInput("storeoptions[language]",null, $row_opt->language);
        $encloseds[]=JHTML::_('select.option','head-top',JText::_('HEADING_TOP'),'opt','desc');
        $encloseds[]=JHTML::_('select.option','head-bot',JText::_('HEADING_BOTTOM'),'opt','desc');
        $encloseds[]=JHTML::_('select.option','body-top',JText::_('BODY_TOP'),'opt','desc');
        $encloseds[]=JHTML::_('select.option','body-bot',JText::_('BODY_BOTTOM'),'opt','desc');
        $lists['enclosed_img']=JHTML::_('select.genericlist',$encloseds,'storeoptions[enclosed_img]','','opt','desc',$row_opt->enclosed_img);
        return array($lists, array("0"=>"0"));
    }
    
    function checkGuid($guid, $sec_avoid = false, $opts)
    {
        $db = &JFactory::getDBO();
        $sect=0;
        if (is_object($opts)) {
            //$sect=$opts->sectionid;
            $categ=$opts->catid;
        } else if (is_array($opts)) {
            //$sect=$opts['sectionid'];
            $categ=$opts['catid'];            
        } else { //if nothing matches, set to zero as default, to avoid problems.
            $categ=0;
        }
        //$tit=substr($db->getEscaped(JRoute::_(html_entity_decode( $title ))),0,100);
        $guid = $db->getEscaped(rawurldecode(html_entity_decode($guid)));
        $sect = $db->getEscaped(rawurldecode($sect));
        $categ = $db->getEscaped(rawurldecode($categ));
    
        //if (!$sec_avoid) { Removed, duplicates are not allowed at all on joomla 1.7.
            $query = "SELECT id FROM #__content WHERE attribs LIKE '%$guid%' OR urls like '%$guid%'";
        /*} else {
            $query = "SELECT id FROM #__content WHERE (attribs LIKE '%$guid%' OR urls like '%$guid%') and sectionid='$sect' AND catid='$categ'";
        }*/
        $db->setQuery($query);
        $db->query();
        $elements = $db->loadObjectList();
        if (count($elements) > 0) {
            $ret = false;
        } else {
            $ret = true;
        }
        unset($elements);
        return $ret;
    }
    
    function checkAlias($alias) {
        $db=&JFactory::getDBO();
        $query="SELECT title_alias FROM #__content WHERE title_alias LIKE '".trim($db->Escape($alias))."%'";
        //$log=new JLog("/tmp/aliaslog");
        //echo "Query: $query";
        $db->setQuery($query);
        $db->query();
        $rows=$db->getNumRows();
        $ret=$alias;
        if ($rows>0) {
            $ret.=($rows+1);
        }
        //$log->addEntry(array("comment"=>"Result: $ret"));
        return $ret;
    }
}