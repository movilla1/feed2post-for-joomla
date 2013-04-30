<?php
/**
 * Feed2post v3.0
 * @author Mario O. Villarroel
 * @copyright 2010 - Elcan Software
 * @license GPLv2
 * 
 * Engine to store the data gathered using zoo content items.
 */
defined('_JEXEC') or die('Restricted Access');
require_once (JPATH_COMPONENT_ADMINISTRATOR . DS . "engines" . DS .
    "F2pEngine.php");
class zoo25F2pEng extends F2pEngine
{
     function store($rowfeed,$config,$itemsrc) {
        require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_zoo".DS."config.php");
        jimport('joomla.utilities.date');
        $uri =& JURI::getInstance();
        $db = &JFactory::getDBO();
        $user = &JFactory::getUser();
        $advertisement = html_entity_decode(urldecode(JRoute::_($row_feed->advert)));
        $siteurl=$uri->base();
        $config =& JFactory::getConfig();
        $tzoffset = $config->getValue('config.offset');

        //$tmpdate = new JDate();
        if (stripos($siteurl,"/administrator/") == (strlen($siteurl)-15)) {
             $siteurl=substr($siteurl,0,strlen($siteurl)-15)."/";
        }
        
        $opts=json_decode(rawurldecode($rowfeed->storeoptions));
        
        $zoo = App::getInstance('zoo');
        $tmpdate=($opts->createddate==1) ? new JDate($item->date,$tzoffset):new JDate($row->created, $tzoffset);
        $createddate=$tmpdate->toMySQL();
        $publish=$tmpdate->toMySQL();
        if ($opts->autounpublish==1) {
            $publish_down = time() + $opts->validfor;
            $date = new JDate($publish_down, $tzoffset);
            $unpublish= $date->toMySQL();
        } else {
            $unpublish="0000-00-00 00:00:00";
        }
        $joomla = $zoo->zoo->app->system->application;
        if ($joomla->isAdmin()) {
            $zoo->system->application->setUserState('com_zooapplication', $opts->application);
        } else { //for cronjob to work
            JRequest::setVar("app_id",$opts->application,"GET",true);
            JRequest::setVar("app_id",$opts->application,"POST",true);
        }
        
        $application = $zoo->zoo->getApplication();
        
        $item_table			= $zoo->zoo->app->table->item;
        //$category_table		= $zoo->zoo->app->table->category;
        $type_obj           = $application->getType('article');
		$elements			= $type_obj->getElements();


        $now				= $zoo->zoo->app->date->create();
	/*	$app_categories		= $application->getCategories();
		$app_categories		= array_map(create_function('$cat', 'return $cat->name;'), $app_categories);*/
        $item = $zoo->zoo->app->object->create('Item');
		$item->application_id = $application->id;
		$item->type = $type_obj->id;
        
        $tags = array();
        $tagsorig=split(",",$opts->tags);
        foreach ($tagsorig as $tag) {
			$tags[] = (string)$tag;
		}
		$item->setTags($tags);

		// set access
		$item->access = $zoo->zoo->app->joomla->getDefaultAccess();

		// store created by
        $name=$db->getEscaped(stripslashes($item->title));
        $item->alias = $zoo->zoo->app->string->sluggify(rawurldecode($itemsrc->title));
        $item->alias = $zoo->zoo->app->alias->item->getUniqueAlias(0, $item->alias);
        
		$item->created_by  = $db->getEscaped($opts->posterid);

		// set created
		$item->created	   = $createddate;

		// store modified_by
		$createdby = $opts->posterid;
		$item->modified_by = $createdby;

		// set modified
		$item->modified	   = $now->toMySQL();

		// store element_data and item name
		$item_categories = array();
        $name=$db->getEscaped(str_replace(array("\\n","\n"),"",rawurldecode($itemsrc->title)));
        $item->name = stripslashes($name);
        $item->unpublish = $unpublish;
        
        $descript=($opts->intrometa==1)? substr(strip_tags(rawurldecode($itemsrc->description)),0,180):"";
        $author=($opts->origauthor==1) ? rawurldecode($itemsrc->author):"";
        $item->created_by_alias=$author;
        
        $access=0; //not sure if this is the access group...
        $searchable=($opts->searchable==1)? 1:0;
        
        $item_categories[]=$opts->category;
        if ($opts->frontpage==1) {
            $item_categories[]=0;
        }
        $item->elements = $zoo->zoo->app->data->create();
        foreach ($elements as $assignment=>$val) {
            $element_data = array();
            switch ($elements[$assignment]->getElementType()) {
                case 'text':
                    $element_data['0'] = array('value' => stripslashes(rawurldecode($itemsrc->title)));
                    break;                    
                case 'textarea':
                    $element_data['0'] = array('value' => stripslashes(rawurldecode($itemsrc->description))."<br/>".stripslashes(rawurldecode($itemsrc->content))."\n<!--".stripslashes(stripslashes($itemsrc->guid))."-->");
                    break;
                case 'link':
        			$linkbackText = (isset ($rowfeed->linkbacktext) && strlen(trim($rowfeed->linkbacktext))>2) ? trim($rowfeed->linkbacktext) : JText::_("READ_FULL_ARTICLE");
                	if ($rowfeed->iframelinks == 1) {
                    	$links=rawurlencode(base64_encode(rawurldecode($itemsrc->link)));
                        $lburl="index.php?option=com_feed2post&item={$rowfeed->id}&task=iframe&link=$links";
                        $element_data['0']=array('value'=>$lburl,'text'=>$linkbackText,'rel'=>'no-follow');
                    } else {
                        $element_data['0']=array('value'=>rawurldecode($itemsrc->link),'text'=>$linkbackText,'rel'=>'no-follow');
                    }    
                    break;				    
				case 'socialbookmarks':
                    $element_data['0'] = $opts->social;
                    break;
                case 'image':
				case 'download':
                    $element_data = array('file'=>rawurldecode($this->strip_host($itemsrc->images[0])),'title'=>substr(rawurldecode($itemsrc->title),0,40));
                    //if (strlen(trim($element_data['0']))<=0) unset($element_data['0']);
                    //$element_data['link'] = array('value'=>$itemsrc->images[0],'text'=>"",'rel'=>'no-follow');
                    break;
            }
            if (count($element_data)>0) {
             $element_obj = $item->getElement($assignment);
             $element_obj->bindData($element_data);
/*             echo $assignment."<br/>\n";
             print_r($element_obj->get("textarea"));
             print_r($element_data);
             echo "<br/><br/>\n";*/
            }
        }
        
        /*$elements_string = '<?xml version="1.0" encoding="UTF-8"?><elements>';
		foreach ($elements as $element) {
		      print_r($element);
			$elements_string .= $element->toXML();
		}
        $elements_string .= "<guid><![CDATA[".$itemsrc->guid."]]></guid>\n";
		$elements_string .= '</elements>';
		$item->elements = $elements_string;*/
        
        $item->getParams()->set('metadata.description',$descript);
        $item->getParams()->set('metadata.author',$author);
        $item->getParams()->set('config.enable_comments',$opts->comments);
        $item->getParams()->set('config.primary_category',$opts->category);
        $item->getParams()->set("metadata.guid",md5($itemsrc->guid));
        $item->state = 1;
        try {
        		/*print_r($item);
        		exit();*/
				$item_table->save($item);
				$item_id = $item->id;

                $zoo->zoo->app->category->saveCategoryItemRelations($item_id, $item_categories);
        } catch (ItemTableException $e) {
            echo "ERROR!! Please contact support";
            return false;
        }
        return true;
     }
     
     function showEngineSettings($row) {
        $lists=$this->getLists($row);
        ob_start();
?>
    <script type="text/javascript">
<!--
var appcategories = new Array;
 
<?php
$i = 0;
/*print_r($lists['applicationCategories']);
return;*/
foreach ($lists['applicationCategories'] as $v) {
	echo "appcategories[" . $i++ . "] = new Array( '$v[id]','" . addslashes($v['application_id']) .
		"','" . addslashes($v['name']) . "' );\n\t\t";
}
echo "\r\n";

$document = &JFactory::getDocument();
$document->addStyleSheet(JURI::base().'components/com_feed2post/css/backend.css');
?>
-->
</script>
<fieldset class="adminform">
<legend><?php echo JText::_("Zoo Configuration");echo JHTML::tooltip(JText::_('ZOO_ITEM_TOOLTIP'), JText::_("ZOO_ITEM"), 'tooltip.png', '', '', false);?>
    </legend>
<table align="center" class="admintable" width="100%">
   <tr >
   <th class="F2P-form-label">
        <?php echo JHTML::tooltip(JText::_('zoo_application_Tooltip'), JText::_("Application"), '', JText::_("Application:"));?>
   </th>
   <td><?php echo $lists['applications'] ?></td>
   <th class="F2P-form-label">
        <?php echo JHTML::tooltip(JText::_("zoo_category_Tooltip", JText::_("Category"),"",JText::_("Category:"))); ?> 
   </th>
   <td><?php echo $lists['categories']?></td>
 </tr>
 <tr>
  <th class="F2P-form-label">
    <?php echo JHTML::tooltip(JText::_("zoo_published_Tooltip"), JText::_("Published"),'',JText::_("Published:"));?>
  </th>
  <td><fieldset class="radio inputbox"><?php echo $lists['published']?></fieldset></td>
   <th class="F2P-form-label">
    <?php echo JHTML::tooltip(JText::_("zoo_searchable_tooltip"),JText::_("Searchable"),"",JText::_("Searchable:"));?>
   </th>
   <td><fieldset class="radio inputbox"><?php echo $lists['searchable']?></fieldset></td>
 </tr>
 <tr>
   <th class="F2P-form-label">
    <?php echo JHTML::tooltip(JText::_("zoo_comments_tooltip"),JText::_("Comments"),"",JText::_("Comments:"));?>
   </th>
   <td><fieldset class="radio inputbox"><?php echo $lists['comments']?></fieldset></td>
   <th class="F2P-form-label">
    <?php echo JHTML::tooltip(JText::_("zoo_frontpage_tooltip"),JText::_("Front Page"),"",JText::_("Front Page:"));?>
   </th>
   <td><fieldset class="radio inputbox"><?php echo $lists['frontpage']?></fieldset></td>
 </tr>
 <tr>
   <th class="F2P-form-label">
    <?php echo JHTML::tooltip(JText::_("zoo_social_tooltip"),JText::_("Social"),"",JText::_("Social:"));?>
   </th>
   <td><fieldset class="radio inputbox"><?php echo $lists['social']?></fieldset></td>
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
   <th class="F2P-form-label" ><?php
        echo JHTML::tooltip(JText::_('VALID_FOR_TOOLTIP'), JText::_("VALID_FOR"), '',
            JText::_("VALID_FOR"));
?>:</th>
   <td  colspan="1"><?php  echo $lists['validfor']?></td>
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
     <th><?php echo JHTML::tooltip(JText::_('tags_tooltip'), JText::_("Tags"),
            '', JText::_("Tags"))?></th>
     <td><?php echo $lists['tags'] ?></td> 
 </tr>
</table>
</fieldset>
<?php
        $panneldata=ob_get_contents();
        ob_end_clean();
        return $panneldata;
     }
     
     function checkGuid($guid, $sec_avoid = false, $opts) {
        $db =& JFactory::getDBO();

        $guid = $db->getEscaped(str_replace(array("\\n","\n"),"",rawurldecode(html_entity_decode($guid))));
        $guid=md5($guid);
        $query="SELECT id FROM #__zoo_item WHERE name LIKE '%$guid%' OR elements LIKE '%$guid%' OR params LIKE '%$guid%'";
//        echo $query."<br/>";
        
        $db->setQuery($query);
        $db->query();
        $elements = $db->loadObjectList();
        if (count($elements) > 0 || $db->getErrorNum()) {
            $ret = false;
        } else {
            $ret = true;
        }
        unset($db);
        unset($elements);
        return $ret;
     }
          
     function getLists($row) {
        $db = &JFactory::getDBO();
        $user = &JFactory::getUser();
        $query = "SELECT username,id FROM #__users WHERE 1";
        $db->setQuery($query);
        $users[] = JHTML::_('select.option', '0', JText::_('Please select'), 'id',
            'username');
        $users = array_merge($users, $db->loadObjectList());
        $lists['users'] = JHTML::_('select.genericlist', $users, 'storeoptions[posterid]','class="inputbox" size="1" ', 'id', 'username', intval($opts->posterid));
        
        $queryaps="SELECT id,application_group,name FROM #__zoo_application WHERE 1";
        $db->setQuery($queryaps);
        $db->query();
        if ($db->ErrorNo()==0) {
            $apps=$db->loadAssocList();
        } else {
            $apps=array("0"=>"<b>No applications found</b>");
        }
        // $queryCategories="SELECT id,name,application_id,params FROM #__zoo_category WHERE 1";
        // CNC - Multiple zoo apps have the same catagory name so we need the app name in the list
        $queryCategories="SELECT c.id,CONCAT(a.name,' -> ',c.name) AS `name`,c.application_id,c.params FROM #__zoo_category as c LEFT JOIN #__zoo_application AS a ON (c.application_id=a.id) WHERE 1 ORDER BY name";
        $db->setQuery($queryCategories);
        $db->query();
        if ($db->ErrorNo()==0) {
            $categs=$db->loadAssocList();
        } else {
            $categs=array("0"=>"<b>No Categories found</b>");
        }
        $lists['applicationCategories']=$categs;
        $opts=json_decode(rawurldecode($row->storeoptions));
        $javascript="lis=document.getElementByID('storeoptionscategory');changeDynaList( 'storeoptionscategory', appcategories, lis.options[lis.selectedIndex].value, 0, 0);changeDynalist('storeoptionstemplate',template,lis.options[lis.selectedIndex].value,0,0)";
        $lists['applications']=JHTML::_("select.genericlist",$apps,'storeoptions[application]',"class='inputbox' size='1' $javascript","id","application_group",intval($opts->application));
        $lists['categories']=JHTML::_("select.genericlist",$categs,'storeoptions[category]',"class='inputbox' size='1'","id","name",intval($opts->category));
        $lists['templates']="";//JHTML::_("select.genericlist",$templates,'storeoptions[template]',"class='inputbox' size='1'","appgroup","name",intval($opts->template));
        $lists['published']=JHTML::_("select.booleanlist","storeoptions[published]","class='inputbox'",$opts->published);
        $lists['searchable']=JHTML::_("select.booleanlist","storeoptions[searchable]","class='inputbox'",$opts->searchable);
        $lists['comments']=JHTML::_("select.booleanlist","storeoptions[comments]","class='inputbox'",$opts->comments);
        $lists['frontpage']=JHTML::_("select.booleanlist","storeoptions[frontpage]","class='inputbox'",$opts->frontpage);
        $lists['social']=JHTML::_("select.booleanlist","storeoptions[social]","class='inputbox'",$opts->social);
        $lists['intrometa'] = JHTML::_('select.booleanlist', 'storeoptions[intrometa]', 'class="inputbox"', $opts->intrometa);
        $author[] = JHTML::_('select.option', '0', JText::_("Don't store"), 'id',
            'title');
        $author[] = JHTML::_('select.option', '1', JText::_('As article Auhtor'),
            'id', 'title');
        $author[] = JHTML::_('select.option', '2', JText::_('As Text'), 'id', 'title');
        $lists['origauthor'] = JHTML::_('select.genericlist', $author, 'storeoptions[origauthor]','class="inputbox" size="1"', "id", "title", $opts->origauthor);
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
            'class="inputbox"', 'value', 'text', intval($opts->validfor));
        $lists['autounpub'] = JHTML::_('select.booleanlist', 'storeoptions[autounpublish]',
            'class="inputbox"', $opts->autounpublish);
        $lists['createddate'] = JHTML::_('select.booleanlist', 'storeoptions[createddate]',
            'class="inputbox"', $opts->createddate);
        $lists['tags']="<input type='text' value='".$opts->tags."' name='storeoptions[tags]' id='store_tags' size='60' />";  
        return $lists;
     }
     
     function strip_host($url) {
		$parts=parse_url($url);
		$frag=(isset($parts['fragment'])) ? "#".$parts['fragment']:"";
		$returl=$parts['path']."?".$parts['query'].$frag;
		return $returl;
	 }
}