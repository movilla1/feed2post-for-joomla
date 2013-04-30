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

class contentF2pEng extends F2pEngine
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
        $fullText = $content;
        $fullText .= ($opts->origdate == 1) ? "<br/>\n" . JText::_("Posted") . ": $date" : "";
        $linkbackText = (strlen(trim($row_feed->linkbacktext))>2) ? trim($row_feed->linkbacktext) : JText::_("READ_FULL_ARTICLE");
        if ($row_feed->includelink == "1") {
            $site = $uri->root() . "/components/com_feed2post/feed2post.php";
            if ($row_feed->iframelinks == 1) {
                $llink=rawurlencode(base64_encode($link));
                $linkback="<div id='article_full_f2p'><a href='".$uri->root()."index.php?option=com_feed2post&item={$row->id}&task=iframe&link=$llink'>$linkbackText</a></div>\n";
            } else {
                $linkback = "<div id='article_full_f2p'><br/><a href='$link' target='_blank'>". $linkbackText . " </a></div>";
            }
        }
        $tmp="";
        foreach ($item->images as $imgurl) {
        	$tmp.="<div class='f2p_image'><img src='$imgurl' border='0' alt='Image'></div>";
        }
        jimport('joomla.utilities.date');

        // Initialize variables
        $row =& JTable::getInstance('content');
        $config =& JFactory::getConfig();
        $tzoffset = $config->getValue('config.offset');

        $tmpdate = new JDate($row->created, $tzoffset);
        $row->checkout($user->get('id')); //checkit out by me.
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
        $row->sectionid = $opts->sectionid;
        $row->catid = $opts->catid;
        $row->version = 0;
        $row->state = 1;
        $row->ordering = 0;
        $row->images = array();
        $row->access = $opts->acgroup;
$row->attribs = "show_title=
link_titles=
show_intro=" . $opts->showintro . "
show_section=
link_section=
show_category=
link_category=
show_vote=
show_author=
show_create_date=
show_modify_date=
show_pdf_icon=
show_print_icon=
show_email_icon=
language=
keyref=" . $guid . "
readmore=".$opts->alternativereadmore;

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
        $row->title_alias = JRoute::_(html_entity_decode($title));
        $row->title = JRoute::_(html_entity_decode($title, ENT_QUOTES), true); // SEO aware
        if (strlen(trim($row->title)) <= 0) {
            $row->title = $row_feed->title . " - " . Date("Y-m-d H:i:s");
        }

        if (!$row->check()) {
            JError::raiseError(500, $db->stderr());
            return false;
        }
        // Increment the content version number
        $row->version++;

        // Store the content to the database
        if (!$row->store()) {
            JError::raiseError(501, $db->stderr());
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
        require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_frontpage' .
            DS . 'tables' . DS . 'frontpage.php');
        $fp = new TableFrontPage($db);

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
<!--
var sectioncategories = new Array;
<?php
        $i = 0;
        foreach ($sectioncategories as $k => $items) {
            foreach ($items as $v) {
                echo "sectioncategories[" . $i++ . "] = new Array( '$k','" . addslashes($v->id) .
                    "','" . addslashes($v->title) . "' );\n\t\t";
            }
        }
?>
-->
</script>
<table align="center" class="adminlist">
   <tr >
   <th style="width: 140px"><?php
        echo JHTML::tooltip(JText::_('Section_Tooltip'), JText::_("Section"), '', JText::
            _("Section"));
?>:</th>
   <td><?php
        echo $lists['sectionid'];
?></td> 
  <th style="width: 140px"><?php
        echo JHTML::tooltip(JText::_('Category_Tooltip'), JText::_("Category"), '',
            JText::_("Category"));
?>:</th>
  <td ><?php
        echo $lists['catid'];
?></td>
  </tr>
  <tr>
   <th style="width: 140px"><?php
        echo JHTML::tooltip(JText::_('Auto_Unpublish_Tooltip'), JText::_("Auto Unpublish"),
            '', JText::_("Auto Unpublish"));
?>:</th>
   <td> <?php
        echo $lists["autounpub"];
?></td>
   <th style="width: 140px" ><?php
        echo JHTML::tooltip(JText::_('VALID_FOR_TOOLTIP'), JText::_("VALID_FOR"), '',
            JText::_("VALID_FOR"));
?>:</th>
   <td  colspan="1"><?php
        echo $lists['validfor']
?></td>
  </tr> 
  <tr >
   <th style="width: 140px"><?php
        echo JHTML::tooltip(JText::_('Include_Original_Date_Tooltip'), JText::_("Include Original Date"),
            '', JText::_("Include Original Date"));
?>:</th>
   <td ><?php echo $lists['origdate']; ?></td>
   <th style="width: 140px"><?php
        echo JHTML::tooltip(JText::_('Set_feed_date_as_created_date_Tooltip'), JText::_
            ("Set feed date as created date"), '', JText::_("Set feed date as created date"));
?>*</th>
   <td><?php echo $lists['createddate'];?></td>
  </tr>
  <tr>
      <th style="width: 140px">
       <?php
        echo JHTML::tooltip(JText::_('Alternative_Read_More_Tooltip'), JText::_("Alternative Read More"),
            '', JText::_("Alternative Read More"));
?>:
      </th>
      <td><?php echo $lists['alternativereadmore']?> </td>
      <th style="width: 140px"><?php
        echo JText::_("Intro_AS_Meta")
?></th>
      <td><?php echo $lists['intrometa'] ?><?php echo JHTML::tooltip(JText::_('introMeta_Tooltip'), JText::_("Intro_As_Meta"),
            'tooltip.png', '', '', false);?></td>
  </tr>
  <tr>
     <th style="width: 140px"><?php
        echo JHTML::tooltip(JText::_('Post_as_user_Tooltip'), JText::_("Post as user"),
            '', JText::_("Post as user"));
?>:</th>
     <td><?php echo $lists['users'];?></td> 	
     <th style="width: 140px"><?php
        echo JHTML::tooltip(JText::_('Store_Author_Tooltip'), JText::_("Store Author"),
            '', JText::_("Store Author"));
?></th>
     <td><?php  echo $lists['origauthor'];?></td>
  </tr>
  <tr>
	 	<th style="width: 140px"><?php
        echo JHTML::tooltip(JText::_('Access_Group_Tooltip'), JText::_("Access Group"),
            '', JText::_("Access Group"));?>:</th>
  		<td><?php  echo $lists['accessgrp']; ?></td>
  		   <th style="width: 140px"><?php echo JHTML::tooltip(JText::_('Front_Page_Tooltip'), JText::_("FRONT_PAGE"), '', JText::_("FRONT_PAGE"));?>:</th>
   <td> <?php echo $lists['frontpage']; ?></td>
  </tr>
  <tr>
    <th style="width: 140px"><?php echo JHTML::tooltip(JText::_('Show_intro_Tooltip'), JText::_("SHOW_INTRO"), '', JText::_("SHOW_INTRO"));?>:</th>
    <td><?php echo $lists['showintro'];?></td>
    <th style="width: 140px;"></th>
    <td></td>
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
        $users[] = JHTML::_('select.option', '0', JText::_('PLEASE_SELECT'), 'id',
            'username');
        $users = array_merge($users, $db->loadObjectList());
        $lists['users'] = JHTML::_('select.genericlist', $users, 'storeoptions[posterid]',
            'class="inputbox" size="1" ', 'id', 'username', intval($row_opt->posterid));

        $javascript = "onchange=\"lis = document.getElementById('storeoptionssectionid');changeDynaList( 'storeoptionscatid', sectioncategories, lis.options[lis.selectedIndex].value, 0, 0);\"";
//document.adminForm.storeoptions\[sectionid\].options[document.adminForm.storeoptions\[sectionid\].selectedIndex].value
        $query = 'SELECT s.id, s.title' . ' FROM #__sections AS s' .
            ' ORDER BY s.ordering';
        $db->setQuery($query);
        $sections[] = JHTML::_('select.option', '-1', '- ' . JText::_('SELECT_SECTION') .
            ' -', 'id', 'title');
        $sections[] = JHTML::_('select.option', '0', JText::_('Uncategorized'), 'id',
            'title');
        $sections = array_merge($sections, $db->loadObjectList());
        $lists['sectionid'] = JHTML::_('select.genericlist', $sections, 'storeoptions[sectionid]',
            'class="inputbox" size="1" ' . $javascript, 'id', 'title', intval($row_opt->
            sectionid));

        foreach ($sections as $section) {
            $section_list[] = (int)$section->id;
            // get the type name - which is a special category
            if ($row !== false) {
                if ($row->sectionid) {
                    if ($section->id == $row_opt->sectionid) {
                        $contentSection = $section->title;
                    }
                } else {
                    if ($section->id == $row_opt->sectionid) {
                        $contentSection = $section->title;
                    }
                }
            }
        }

        $sectioncategories = array();
        $sectioncategories[-1] = array();
        $sectioncategories[-1][] = JHTML::_('select.option', '-1', JText::_('SELECT_CATEGORY'),
            'id', 'title');
        $section_list = implode('\', \'', $section_list);
        $query = 'SELECT id, title, section' . ' FROM #__categories' .
            ' WHERE section IN ( \'' . $section_list . '\' )' . ' ORDER BY ordering';
        $db->setQuery($query);
        $cat_list = $db->loadObjectList();

        // Uncategorized category mapped to uncategorized section
        $uncat = new stdClass();
        $uncat->id = 0;
        $uncat->title = JText::_('Uncategorized');
        $uncat->section = 0;
        $cat_list[] = $uncat;
        foreach ($sections as $section) {
            $sectioncategories[$section->id] = array();
            $rows2 = array();
            foreach ($cat_list as $cat) {
                if ($cat->section == $section->id) {
                    $rows2[] = $cat;
                }
            }
            foreach ($rows2 as $row2) {
                $sectioncategories[$section->id][] = JHTML::_('select.option', $row2->id, $row2->
                    title, 'id', 'title');
            }
        }
        $sectioncategories['-1'][] = JHTML::_('select.option', '-1', JText::_('SELECT_CATEGORY'),
            'id', 'title');
        $categories = array();
        foreach ($cat_list as $cat) {
            if ($cat->section == $row_opt->sectionid)
                $categories[] = $cat;
        }

        $categories[] = JHTML::_('select.option', '-1', JText::_('SELECT_CATEGORY'),
            'id', 'title');
        $lists['catid'] = JHTML::_('select.genericlist', $categories, 'storeoptions[catid]',
            'class="inputbox" size="1"', 'id', 'title', intval($row_opt->catid));
        $lists['autounpub'] = JHTML::_('select.booleanlist', 'storeoptions[autounpublish]',
            'class="inputbox"', $row_opt->autounpublish);
        $lists['showintro'] = JHTML::_('select.booleanlist', 'storeoptions[showintro]',
            'class="inputbox"', $row_opt->showintro);
        $lists['intrometa'] = JHTML::_('select.booleanlist', 'storeoptions[intrometa]',
            'class="inputbox"', $row_opt->intrometa);
        $author[] = JHTML::_('select.option', '0', JText::_("Don't store"), 'id',
            'title');
        $author[] = JHTML::_('select.option', '1', JText::_('As article Auhtor alias'),
            'id', 'title');
        $author[] = JHTML::_('select.option', '2', JText::_('As Text'), 'id', 'title');
        $lists['origauthor'] = JHTML::_('select.genericlist', $author, 'storeoptions[origauthor]',
            'class="inputbox" size="1"', "id", "title", $row_opt->origauthor);

        $lists['frontpage'] = JHTML::_('select.booleanlist', 'storeoptions[frontpage]', 'class="inputbox"', $row_opt->frontpage);
        $valids[] = JHTML::_('select.option', '-1', JText::_('Ever'), 'value', 'text');
        $valids[] = JHTML::_('select.option', '84200', JText::_('1_DAY'), 'value',
            'text');
        $valids[] = JHTML::_('select.option', '604800', JText::_('7_DAYS'), 'value',
            'text');
        $valids[] = JHTML::_('select.option', '1209600', JText::_('14_DAYS'), 'value',
            'text');
        $valids[] = JHTML::_('select.option', '2592000', JText::_('30_DAYS'), 'value',
            'text');
        $valids[] = JHTML::_('select.option', '5184000', JText::_('60_DAYS'), 'value',
            'text');
        $valids[] = JHTML::_('select.option', '7776000', JText::_('90_DAYS'), 'value',
            'text');
        $valids[] = JHTML::_('select.option', '15552000', JText::_('180_DAYS'), 'value',
            'text');
        $valids[] = JHTML::_('select.option', '23328000', JText::_('270_DAYS'), 'value',
            'text');
        $valids[] = JHTML::_('select.option', '31536000', JText::_('365_DAYS'), 'value',
            'text');
        $lists['validfor'] = JHTML::_('select.genericlist', $valids, 'storeoptions[validfor]',
            'class="inputbox"', 'value', 'text', intval($row_opt->validfor));
        $delays[] = "No Delay";
        $lists['delay'] = JHTML::_('select.genericlist', $delays, 'storeoptions[delay]',
            'class="inputbox"', 'id', 'title', intval($row_opt->delay));
        $lists['origdate'] = JHTML::_('select.booleanlist', 'storeoptions[origdate]',
            'class="inputbox"', $row_opt->origdate);
        $lists['createddate'] = JHTML::_('select.booleanlist', 'storeoptions[createddate]',
            'class="inputbox"', $row_opt->createddate);
        $query = "Select id,name FROM #__groups WHERE 1";
        $db->setQuery($query);
        $group_list = $db->loadObjectList();
        foreach ($group_list as $lrow) {
            $optgrp[] = JHTML::_("select.option", $lrow->id, $lrow->name, "value", "text");
        }
        $lists['accessgrp'] = JHTML::_("select.genericlist", $optgrp, "storeoptions[acgroup]",
            "class='inputbox'", "value", "text", intval($row_opt->acgroup));
            
        $lists['alternativereadmore'] = "<input type=\"text\" value=\"".$row_opt->alternativereadmore."\" ";
        $lists['alternativereadmore'].="name=\"storeoptions[alternativereadmore]\" size=\"15\" maxlength=\"20\" />";
        return array($lists, $sectioncategories);
    }
    
    function checkGuid($guid, $sec_avoid = false, $opts)
    {
        $db = &JFactory::getDBO();
        if (is_object($opts)) {
            $sect=$opts->sectionid;
            $categ=$opts->catid;
        } else if (is_array($opts)) {
            $sect=$opts['sectionid'];
            $categ=$opts['catid'];            
        } else { //if nothing matches, set to zero as default, to avoid problems.
            $sect=0;
            $categ=0;
        }
        //$tit=substr($db->getEscaped(JRoute::_(html_entity_decode( $title ))),0,100);
        $guid = $db->getEscaped(rawurldecode(html_entity_decode($guid)));
        $sect = $db->getEscaped(rawurldecode($sect));
        $categ = $db->getEscaped(rawurldecode($categ));
    
        if (!$sec_avoid) {
            $query = "SELECT id FROM #__content WHERE attribs LIKE '%$guid%' OR urls like '%$guid%'";
        } else {
            $query = "SELECT id FROM #__content WHERE (attribs LIKE '%$guid%' OR urls like '%$guid%') and sectionid='$sect' AND catid='$categ'";
        }
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
}