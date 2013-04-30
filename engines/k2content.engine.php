<?php
/**
 * Feed2post v3.0
 * @author Mario O. Villarroel
 * @copyright 2010 - Elcan Software
 * @license GPLv2
 * 
 * Engine to store the data gathered using K2 Content items.
 */
defined('_JEXEC') or die('Restricted Access');
require_once (JPATH_COMPONENT_ADMINISTRATOR . DS . "engines" . DS . "F2pEngine.php");
class k2contentF2pEng extends F2pEngine
{
    
    function store($row,$configs_fp , $item)
    {
  		jimport('joomla.filesystem.file');
		jimport('joomla.html.parameter');
		jimport( 'joomla.utilities.xmlelement' );
		$mainframe = &JFactory::getApplication();
		$db = &JFactory::getDBO();
        $uri =& JURI::getInstance();
        $user = &JFactory::getUser();
        $advertisement = html_entity_decode(urldecode(JRoute::_($row_feed->advert)));
        $siteurl=$uri->base();
        $config =& JFactory::getConfig();
        $tzoffset = $config->getValue('config.offset');

        $tmpdate=($opts->createddate==1) ? new JDate($item->date,$tzoffset):new JDate($row->created, $tzoffset);
        if (stripos($siteurl,"/administrator/") == (strlen($siteurl)-15)) {
             $siteurl=substr($siteurl,0,strlen($siteurl)-15)."/";
        }
        
        $opts=json_decode(rawurldecode($row->storeoptions));
        $publish=$tmpdate->toMySQL();
        if ($opts->autounpublish==1) {
            $publish_down = time() + $opts->validfor;
            $date = new JDate($publish_down, $tzoffset);
            $unpublish= $date->toMySQL();
        } else {
            $unpublish="0000-00-00 00:00:00";
        }

        $xml = new JXMLElement(JFile::read(JPATH_ADMINISTRATOR.DS."components".DS."com_k2".DS.'models'.DS.'item.xml'));
		$itemParams = new JParameter('');
		foreach ($xml->params as $paramGroup) {
			foreach ($paramGroup->param as $param) {
				if ($param->getAttribute('type') != 'spacer' && $param->getAttribute('name')) {
					$itemParams->set($param->getAttribute('name'), $param->getAttribute('default'));
				}
			}
		}
		$itemParams = $itemParams->toString();
        $query = "SELECT id, name FROM #__k2_tags";
		$db->setQuery($query);
		$tagsfromdb = $db->loadObjectList();

		if(is_null($tags))
		$tags = array();
        $link = urldecode($item->link);
        $title = html_entity_decode($item->title);
        $date = urldecode($item->date);
        $content = urldecode($item->content);
        $guid = urldecode($item->guid);
        $author = urldecode($item->author);
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
        $introTextOrig = "<div id='article_intro_f2p'>" . stripslashes(urldecode($item->description)) . "</div>";
        $fullTextOrig = $content;
        $fullTextOrig .= ($opts->origdate == 1) ? "<br/>\n" . JText::_("Posted") . ": $date" : "";
        $introText="";
        $fullText="";
        if ($row->fulltext == "1" || strlen(trim($row->cutat)) > 0) {
            $fullText = ($configs_fp->insertadvert == "1") ? $advertisement : "";
            $fullText .= "<br/>\n" . str_replace('<br>', '<br />', $fullTextOrig);
            if ($opts->origauthor == 2) $row->fulltext .= JText::_("Author:") . $author;
            $fullText.= ($configs_fp->includelink == "1") ? $linkback : "";
        } else { //No full text, all into introtext
            $introText .= ($configs_fp->insertadvert == "1") ? $advertisement : "";
            if ($opts->origauthor == 2) $introtext .= JText::_("Author:") . $author;
        }
        $introText.=$introTextOrig;
        $introText .= ($configs_fp->includelink == "1") ? $linkback : "";
        JTable::addIncludePath(JPATH_ADMINISTRATOR.DS."components".DS."com_k2".DS."tables");
		$K2Item = &JTable::getInstance('K2Item', 'Table');
        $name=$db->getEscaped(str_replace(array("\\n","\n"),"",rawurldecode($item->title)));
		$K2Item->title = stripslashes($name);
		$K2Item->alias = stripslashes($name);
		$K2Item->catid = $opts->category;
		$K2Item->trash = 0;
		$K2Item->published = $opts->published;
		$K2Item->featured = $opts->frontpage;
		$K2Item->introtext = $introText;
		$K2Item->fulltext = $fullText;
		$K2Item->created = $created;
		$K2Item->created_by = $opts->users;
		$author=($opts->origauthor==1) ? rawurldecode($item->author):"";
        $K2Item->created_by_alias = $author;
		$K2Item->modified = $created;
		$K2Item->modified_by =$opts->users;
		$K2Item->publish_up = $publish;
		$K2Item->publish_down = $unpublish;
		$K2Item->access =  $opts->access;
		//$K2Item->ordering = $item->ordering;
		//$K2Item->hits = $item->hits;
        $descript=($opts->intrometa==1)? substr(strip_tags(rawurldecode($itemsrc->description)),0,180):"";
		$K2Item->metadesc = $descript;
		//$K2Item->metadata = ;
		$K2Item->metakey = stripslashes(stripslashes($item->guid));
		$K2Item->params = $itemParams;
		$K2Item->language = $opts->language;
        $K2Item->image = (is_array($item->images))? $item->images[0]:$item->images;
		$K2Item->check();
		$K2Item->store();

        $tags = array();
        $tagsorig=split(",",$opts->tags);
        foreach ($tagsorig as $tag) {
			$tags[] = (string)$tag;
		}
		foreach ( $tags as $itemTag ) {
			$itemTag = JString::trim ( $itemTag );
			if (in_array ( $itemTag, JArrayHelper::getColumn ( $tagsfromdb, 'name' ) )) {
				
				$query = "SELECT id FROM #__k2_tags WHERE name=" . $db->Quote ( $itemTag );
				$db->setQuery ( $query );
				$id = $db->loadResult ();
				$query = "INSERT INTO #__k2_tags_xref (`id`, `tagID`, `itemID`) VALUES (NULL, {$id}, {$K2Item->id})";
				$db->setQuery ( $query );
				$db->query ();
			} else {
				$K2Tag = &JTable::getInstance ( 'K2Tag', 'Table' );
				$K2Tag->name = $itemTag;
				$K2Tag->published = 1;
				$K2Tag->store ();
				$tagsfromdb [] = $K2Tag;
				$query = "INSERT INTO #__k2_tags_xref (`id`, `tagID`, `itemID`) VALUES (NULL, {$K2Tag->id}, {$K2Item->id})";
				$db->setQuery ( $query );
				$db->query ();
			}
		}
				
        return true;
    }
    
    function showEngineSettings($row)
    {
        $lists=$this->getLists($row);
        ob_start();
        $document = &JFactory::getDocument();
        $document->addStyleSheet(JURI::base().'components/com_feed2post/css/backend.css');
?>
<fieldset class="adminform">
	<legend><?php echo JText::_("K2 Configuration");echo JHTML::tooltip(JText::_('K2_ITEM_TOOLTIP'), JText::_("K2_ITEM"), 'tooltip.png', '', '', false);?>
    </legend>
	<table align="center" class="admintable" width="100%">
		<tr>
			<th class="F2P-form-label">
        <?php echo JText::_("JCATEGORY").":";echo JHTML::tooltip(JText::_("CATEGORY_TOOLTIP", JText::_("JCATEGORY"),"",JText::_("JCATEGORY"))); ?> 
   </th>
			<td><?php echo $lists['categories']?></td>
			<th class="F2P-form-label">
    <?php echo JHTML::tooltip(JText::_("published_Tooltip"), JText::_("Published"),'',JText::_("Published:"));?>
  </th>
			<td><fieldset class="radio inputbox"><?php echo $lists['published']?></fieldset></td>
		</tr>
		<tr>
			<th class="F2P-form-label">
    <?php echo JHTML::tooltip(JText::_("K2_FRONTPAGE_TOOLTIP"),JText::_("Front Page"),"",JText::_("Front Page:"));?>
   </th>
			<td><fieldset class="radio inputbox"><?php echo $lists['frontpage']?></fieldset></td>
			<th class="F2P-form-label"><?php echo JText::_("Intro_AS_Meta")?></th>
			<td><fieldset class="radio inputbox"><?php echo $lists['intrometa'] ?><?php echo JHTML::tooltip(JText::_('introMeta_Tooltip'), JText::_("Intro_As_Meta"),
            'tooltip.png', '', '', false);?></fieldset></td>
		</tr>
		<tr>
			<th class="F2P-form-label">
        <?php echo JHTML::tooltip(JText::_('Store_Author_Tooltip'), JText::_("Store Author:"),
            '', JText::_("Store Author"));?>
     </th>
			<td><?php  echo $lists['origauthor'];?></td>
			<th class="F2P-form-label"><?php
        echo JHTML::tooltip(JText::_('Auto_Unpublish_Tooltip'), JText::_("Auto Unpublish:"),
            '', JText::_("Auto Unpublish"));
?>:</th>
			<td><fieldset class="radio inputbox"><?php echo $lists["autounpub"]; ?></fieldset></td>
		</tr>
		<tr>
			<th class="F2P-form-label"><?php
        echo JHTML::tooltip(JText::_('VALID_FOR_TOOLTIP'), JText::_("VALID_FOR"), '',
            JText::_("VALID_FOR"));
?>:</th>
			<td colspan="1"><?php  echo $lists['validfor']?></td>
			<th class="F2P-form-label"><?php
        echo JHTML::tooltip(JText::_('Set_feed_date_as_created_date_Tooltip'), JText::_
            ("Set feed date as created date"), '', JText::_("Set feed date as created date:"));
?>*</th>
			<td><fieldset class="radio inputbox"><?php echo $lists['createddate'];?></fieldset></td>
		</tr>
		<tr>
			<th class="F2P-form-label"><?php
        echo JHTML::tooltip(JText::_('Post_as_user_Tooltip'), JText::_("Post as user"),
            '', JText::_("Post as user"));
?>:</th>
			<td><?php echo $lists['users'];?></td>
			<th><?php echo JHTML::tooltip(JText::_('TAGS_TOOLTIP'), JText::_("Tags"),'', JText::_("Tags"))?></th>
			<td><?php echo $lists['textags'] ?></td>
		</tr>
		<tr>
			<th><?php echo JText::_("Access_Level")?>:</th>
			<td><?php echo $lists['access']?></td>
			<th></th>
			<td>
        <?php echo JHTML::tooltip(JText::_('F2P_K2_TAGS_TOOLTIP'), JText::_("F2P_TAGS"),'', JText::_("F2P_TAGS"));?>
  </td>
		</tr>
	</table>
</fieldset>
<?php
        $panneldata=ob_get_contents();
        ob_end_clean();
        return $panneldata;
    }
    
    function checkGuid($guid, $sec_avoid = false, $opts)
    {
        $db=&JFactory::getDBO();
        $ret=false;
        $query="SELECT id FROM #__k2_items WHERE metakey LIKE '".$guid."' LIMIT 1";
        $db->setQuery($query);
        $res=$db->query();
        if ($res && count($db->loadAssoc())>0) {
            $ret=true;
        }
        return ret;
    }
    
    function getLists($rows) {
        $db=JFactory::getDbo();
        $opts=json_decode(rawurldecode($rows->storeoptions));
        jimport( 'joomla.application.component.model' );
        $lists['published'] = JHTML::_('select.booleanlist', 'storeoptions[published]', 'class="inputbox"', $opts->published);
		$lists['frontpage'] = JHTML::_('select.booleanlist', 'storeoptions[frontpage]', 'class="inputbox"', $opts->frontpage);
        $query = 'SELECT id AS value, title AS text FROM #__usergroups ORDER BY id';
        $db->setQuery( $query );
        $groups = $db->loadObjectList();
		$lists['access'] = JHTML::_('select.genericlist',   $groups, 'storeoptions[access]', 'class="inputbox" size="3"', 'value', 'text', intval( $opts->access ), '', 1 );
        //print_r($opts);
		$query = "SELECT ordering AS value, title AS text FROM #__k2_items WHERE catid={$opts->catid}";
		$lists['ordering'] = JHTML::_('list.specificordering', $rows, $rows->id, $query);
        require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_k2".DS.'models'.DS.'categories.php');
		$categoriesModel= new K2ModelCategories;        
		$categories = $categoriesModel->categoriesTree();
		$lists['categories'] = JHTML::_('select.genericlist', $categories, 'storeoptions[category]', 'class="inputbox"', 'value', 'text', $opts->category);
        $active="";
		$lists['users'] = JHTML::_('list.users', 'storeoptions[users]', $active, false);

        $lists['intrometa'] = JHTML::_('select.booleanlist', 'storeoptions[intrometa]', 'class="inputbox"', $opts->intrometa);
        $lists['autounpub'] = JHTML::_('select.booleanlist', 'storeoptions[autounpub]', 'class="inputbox"', $opts->autounpub);
        $lists['createddate'] = JHTML::_('select.booleanlist', 'storeoptions[createddate]', 'class="inputbox"', $opts->createddate);
        $author[] = JHTML::_('select.option', '0', JText::_("Don't store"), 'id', 'title');
        $author[] = JHTML::_('select.option', '1', JText::_('As article Auhtor'), 'id', 'title');
        $author[] = JHTML::_('select.option', '2', JText::_('As Text'), 'id', 'title');
        $lists['origauthor'] = JHTML::_('select.genericlist', $author, 'storeoptions[origauthor]','class="inputbox" size="1"', "id", "title", $opts->origauthor);
		$valids[] = JHTML::_('select.option', '-1', JText::_('Ever'), 'value', 'text');
        $valids[] = JHTML::_('select.option', '84200', JText::_('1 Day'), 'value','text');
        $valids[] = JHTML::_('select.option', '604800', JText::_('7 Days'), 'value', 'text');
        $valids[] = JHTML::_('select.option', '1209600', JText::_('14 Days'), 'value', 'text');
        $valids[] = JHTML::_('select.option', '2592000', JText::_('30 Days'), 'value', 'text');
        $valids[] = JHTML::_('select.option', '5184000', JText::_('60 Days'), 'value', 'text');
        $valids[] = JHTML::_('select.option', '7776000', JText::_('90 Days'), 'value', 'text');
        $valids[] = JHTML::_('select.option', '15552000', JText::_('180 Days'), 'value', 'text');
        $valids[] = JHTML::_('select.option', '23328000', JText::_('270 Days'), 'value', 'text');
        $valids[] = JHTML::_('select.option', '31536000', JText::_('365 Days'), 'value', 'text');
        $lists['validfor'] = JHTML::_('select.genericlist', $valids, 'storeoptions[validfor]', 'class="inputbox"', 'value', 'text', intval($opts->validfor));
        $lists['textags']= '<input type="text" name="storeoptions[tags]" id="tags" size="50" maxlength="250" value="'.$opts->tags.'" />';
        return $lists;
    }
}

?>