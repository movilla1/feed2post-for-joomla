<?php
defined('_JEXEC') or die('Restricted access');
/**
 * Feed2post v3.0
 * @author Mario O. Villarroel
 * @copyright 2010 - Elcan Software
 * @license GPLv2
 */
jimport('joomla.html.pane');
?>
<script type="text/javascript" language="javascript">
<!--
var enginechanged=false;
function parseScript(_source) {
		var source = _source;
		var scripts = new Array();
		
		// Strip out tags
		while(source.indexOf("<script") > -1 || source.indexOf("</script") > -1) {
			var s = source.indexOf("<script");
			var s_e = source.indexOf(">", s);
			var e = source.indexOf("</script", s);
			var e_e = source.indexOf(">", e);
			
			scripts.push(source.substring(s_e+1, e));
			source = source.substring(0, s) + source.substring(e_e+1);
		}
		
		// Loop through every script collected and eval it
		for(var i=0; i<scripts.length; i++) {
			try {
				eval(scripts[i]);
			}
			catch(ex) {
			     //do nothing when the script fails
			}
		}
		// Return the cleaned source
		return source;
}

function showEngineOpts(rid) {
    if (enginechanged==true) {
        storageElem=document.getElementById('storage');
        val=storageElem.options[storageElem.selectedIndex].value;
        document.getElementById('storeOptionsDiv').innerHTML="Loading...Please Wait...";
        urlrequest="index.php?option=com_feed2post&task=getStoreOpts&id="+rid+"&eng="+val+"&act=1&format=raw";
        var ax = new Ajax(
         urlrequest,{
            method: 'get',
            onComplete: function (resp) {
                document.getElementById('storeOptionsDiv').innerHTML=parseScript(resp);
                enginechanged=false;
            }
         }).request();
     }
}
//-->
</script>
<form action="index.php?option=com_feed2post" method="post" name="adminForm" id="adminForm">
<?php
$pane =& JPane::getInstance('tabs', array('startOffset'=>0));
echo $pane->startPane( 'pane' );
echo $pane->startPanel( JText::_('GENERAL_SETTINGS'), 'panel1' ); 
?>
<style type="text/css">
<!--
th {
  text-align: right;
}
-->
</style>
<fieldset class="adminform">
<legend>
 	Feeds 2 post: <?php  if ($this->row->id) echo JText::_('Edit'); else echo JText::_('New'); ?>
</legend>
  <table class="adminformlist">
  <tr>
   <td><label for="titlef2p"><?php echo JText::_("FEED_TITLE")?>:</label></td>
   <td ><input id="titlef2p" type="text" name="title" value="<?php  echo $this->row->title;?>" size="50" maxlength="100" />   
   </td>
   <td><label><?php echo JText::_("PUBLISHED")?>:</label></td>
   <td><fieldset id="jform_type" class="radio inputbox"><?php echo $this->lists['published'];?></fieldset></td>
  </tr>
  <tr>
   <td><label><?php echo JText::_('Parsers')?>:</label></td>
   <td><?php echo $this->lists['parsers']?></td>
   <td><label><?php echo JText::_('StoreEngine')?>:</label></td>
   <td><?php echo $this->lists['engines']?></td>
  </tr>
  <tr> 
   <td><label><?php echo JHTML::tooltip(JText::_('FEED_URL_TOOLTIP'), 'URL','', 'URL');?>:</label></td>
   <td ><textarea name="feed_url" cols="80" rows="4"><?php echo $this->row->feed_url ?></textarea></td>
  </tr>
  <tr>
  	<td><label><?php echo JHTML::tooltip(JText::_('ADVERTISING_TOOLTIP'), JText::_('Advertising'),'', JText::_("Advertising"));?></label></td> 
  	<td colspan="3"><textarea name="advert" id="advert" cols="80" rows="4"><?php  echo $this->row->advert ?></textarea></td>
  </tr>
  <tr>
   <td><label><?php echo JHTML::tooltip(JText::_('KEYWORDS_TOOLTIP'), JText::_("KeyWords"), 'tooltip.png', JText::_("KeyWords"));?>:</label></td>
   <td colspan="3"><textarea name="keywords" rows="3" cols="80"><?php echo $this->row->keywords ?></textarea></td>
  </tr>
  <tr> 
   <td><label><?php echo JHTML::tooltip(JText::_('NEGATIVE_KEYWORDS_TOOLTIP'), JText::_("NEGATIVE_KEYWORDS"), '', JText::_("NEGATIVE_KEYWORDS"));?>:</label></td>
   <td colspan="3"><textarea name="negkey" rows="3" cols="80"><?php echo $this->row->negkey ?></textarea></td>
  </tr>
 <!-- old sect/categ pos-->
 <!-- Old autounpub pos-->
 <tr>
    <td><label><?php echo JHTML::tooltip(JText::_('FULL_TEXT_GATHER_TOOLTIP'), JText::_("FULL_TEXT"), 'tooltip.png', JText::_('FULL_TEXT'), '', false);?>:<br/><span style="font-size: 8px">(<?php echo JText::_("IF_FEED_FULL")?>)</span></label></td>
    <td><fieldset id="jform_type" class="radio inputbox"><?php echo $this->lists['fulltext']; ?></fieldset></td>
    <td><label><?php echo JHTML::tooltip(JText::_('OPEN_READMORE_LINK_AT_IFRAME_TOOLTIP'), JText::_("OPEN_READMORE_LINK_AT_IFRAME"), '',JText::_("OPEN_READMORE_LINK_AT_IFRAME"));?>:</label></td>
    <td><fieldset id="jform_type" class="radio inputbox"><?php  echo $this->lists['readmorelink']; ?></fieldset></td>
  </tr>  
  <!-- Old origdate pos-->
  <tr>
    <td class="F2P-form-label"><label><?php echo JHTML::tooltip(JText::_('INCLUDE_LINK_TO_ORIGINAL_TOOLTIP'),JText::_("Include link to original"),'',JText::_("Include link to original"))?>:</label></td>
    <td><fieldset id="jform_type" class="radio inputbox"><?php  echo $this->lists['includelink'] ?></fieldset></td>

    <td><label>
    <?php echo JHTML::tooltip(JText::_('IGNORE_ITEM_TOOLTIP'), JText::_("IGNORE_ITEM"), '', JText::_("IGNORE_ITEM"));?>:</label></td>
    <td><fieldset class="radio inputbox"><?php echo $this->lists['ignoreitem']; ?></fieldset></td>
  </tr>
  <!--Old intrometa pos-->
  <tr>
    <td><label><?php echo JHTML::tooltip(JText::_('MINIMUM_CHARACTER_COUNT_TOOLTIP'), JText::_("MINIMUM_CHARACTER_COUNT"), '', JText::_("MINIMUM_CHARACTER_COUNT"));?>:</label></td>
    <td><input type="text" value="<?php  echo $this->row->minimum_count?>" name="minimum_count" size="15" maxlength="20" /></td>
    <td><label>
    <?php echo JHTML::tooltip(JText::_('CUT_AT_LENGTH_TOOLTIP'), JText::_("CUT_AT_LENGTH"), '', JText::_("CUT_AT_LENGTH"));?>:</label></td>
    <td><input type="text" value="<?php  echo $this->row->cutatcharacter?>" name="cutatcharacter" size="15" maxlength="20" /></td>
   </tr> 
  <tr>
    <td><label>
    <?php echo JHTML::tooltip(JText::_('CUT_AT_TAG_TOOLTIP'), JText::_("CUT_AT_TAG"), '',JText::_("CUT_AT_TAG"));?>:</label></td>
    <td><input type="text" value="<?php  echo $this->row->cutat?>" name="cutat" size="15" maxlength="20" /></td>
    <td><label><?php echo JHTML::tooltip(JText::_('REPLACE_IMAGES_TOOLTIP'), JText::_("REPLACE_IMAGES"), '',JText::_("REPLACE_IMAGES"));?>:</label></td></label></td>
    <td><fieldset class="radio inputbox"><?php echo $this->lists['replaceimgs']?></fieldset></td>
  </tr>
  <tr>
    <td><label>
    <?php echo JHTML::tooltip(JText::_('MAX_ITEMS_DESC'), JText::_("MAX_ITEMS"), '',JText::_("MAX_ITEMS"));?>:</label></td>
    <td><input type="text" value="<?php  echo $this->row->maxitems?>" name="maxitems" size="15" maxlength="20" /></td>
    <td><label ><?php echo JHTML::tooltip(JText::_('TRUNCATE_DESC'), JText::_("TRUNCATE"), '',JText::_("TRUNCATE"));?>:</label></td>
    <td><fieldset class="radio inputbox"><?php echo $this->lists['truncate']?></fieldset></td>
  </tr>
  <tr>
    <td><label><?php echo JHTML::tooltip(JText::_('MINKEYWORD_LEN_DESC'), JText::_("MINKEYWORD_LEN"), '',JText::_("MINKEYWORD_LEN"));?>:</label></td>
    <td><input type="text" value="<?php echo $this->row->minkeylen?>" name="minkeylen" size="15" maxlength="20"/></td>
    <td><label><?php echo JHTML::tooltip(JText::_('KEYWORD_COUNT_DESC'), JText::_("KEYWORD_COUNT"), '',JText::_("KEYWORD_COUNT"));?>:</label></td>
    <td><input type="text" value="<?php echo $this->row->keycount?>" name="keycount" size="15" maxlength="20"/></td>
  </tr>
  <!--old acgroup pos-->
  </table>
 </fieldset>
 
<?php
  echo $pane->endPanel();
  echo $pane->startPanel(JText::_("IFRAME_OPTIONS"),"iframeopts");
?>
<fieldset class="adminform">
	<legend><?php echo JText::_( 'IFRAME_CONFIGURATION_OPTIONS' ); ?></legend>
	<table class="adminlist">
	<tr> 
    <th style="width: 160px"><?php echo JText::_("WIDTH")?>:</th>
    <td><input type="text" value="<?php echo $this->row->width;?>" name="width" size="10" maxlength="8" /></td>
    <th style="width: 160px"><?php echo JText::_("HEIGHT")?>:</th>
    <td><input type="text" value="<?php echo $this->row->height;?>" name="height" size="10" maxlength="8" /></td>
  </tr>  
 <tr>
   <th style="width: 160px"><?php echo JText::_("SCROLLING")?>:</th>
    <td><fieldset id="jform_type" class="radio inputbox"><?php  echo $this->lists['scrolling'] ; ?></fieldset></td>
    <th style="width: 160px"><?php echo JText::_("FRAME_BORDER")?>:</th>
    <td><fieldset id="jform_type" class="radio inputbox"><?php  echo $this->lists['frameborder'] ; ?></fieldset></td>
  </tr>  
      <tr>
    <th style="width: 160px"><?php echo JText::_("MARGIN_WIDTH")?>:</th>
    <td><input type="text" value="<?php echo $this->row->marginwidth; ?>" name="marginwidth" size="10" maxlength="8" /></td>
    <th style="width: 160px"><?php echo JText::_("MARGIN_HEIGHT")?>:</th>
    <td><input type="text" value="<?php echo $this->row->marginheight;?>" name="marginheight" size="10" maxlength="8" /></td>
  </tr> 
  <tr>
    <th style="width: 160px"><?php echo JText::_("ALIGN")?>:</th>
    <td><?php  echo $this->lists['align']; ?></td>
	<th style="width: 160px"><?php echo JText::_("CSS")?>:</th>
    <td><input type="text" value="<?php echo $this->row->iframeclass;?>" name="iframeclass" size="10" maxlength="40"/></td>
  </tr>  
  </table>
   </fieldset>
<?php 
    echo $pane->endPanel();
    echo $pane->startPanel(JText::_("HTML_OPTIONS"),"htmlopts");
?>   
   <fieldset class="adminform">
   <legend><?php echo JText::_( 'HTML_TAGS_CONFIGURATION_OPTIONS' ); ?></legend>
   <script type="text/javascript">
   		function allselections() {
   			var e = document.getElementById('allowabletags');
   			e.disabled = true;
   			var i = 0;
   			var n = e.options.length;
   			for (i = 0; i < n; i++) {
   				e.options[i].disabled = true;
   				e.options[i].selected = true;
			}
		}
		function disableselections() {
			var e = document.getElementById('allowabletags');
			e.disabled = true;
			var i = 0;
			var n = e.options.length;
			for (i = 0; i < n; i++) {
				e.options[i].disabled = true;
				e.options[i].selected = false;
			}
		}
		function enableselections() {
			var e = document.getElementById('allowabletags');
			e.disabled = false;
			var i = 0;
			var n = e.options.length;
			for (i = 0; i < n; i++) {
				e.options[i].disabled = false;
			}
		}
	</script>
	<table class="admintable" cellspacing="1">
	<tr>
	<td valign="top">
	<?php echo JText::_( 'ALLOW_HTML_TAGS' ); ?>:
	</td>
	<td>
	<?php 
	
	if (($this->row->allowabletags == 'all')|| !($this->row->id)) { ?>
		<label for="allow-all"><input id="allow-all" type="radio" name="tags" value="all" onclick="allselections();" checked="checked" /><?php echo JText::_( 'All' ); ?></label>
		<label for="allow-none"><input id="allow-none" type="radio" name="tags" value="none" onclick="disableselections();" /><?php echo JText::_( 'None' ); ?></label>
		<label for="allow-select"><input id="allow-select" type="radio" name="tags" value="select" onclick="enableselections();" /><?php echo JText::_( 'Select From List' ); ?></label>
	<?php } elseif ($this->row->allowabletags == 'none') { ?>
		<label for="allow-all"><input id="allow-all" type="radio" name="tags" value="all" onclick="allselections();" /><?php echo JText::_( 'All' ); ?></label>
		<label for="allow-none"><input id="allow-none" type="radio" name="tags" value="none" onclick="disableselections();" checked="checked" /><?php echo JText::_( 'None' ); ?></label>
		<label for="allow-select"><input id="allow-select" type="radio" name="tags" value="select" onclick="enableselections();" /><?php echo JText::_( 'Select From List' ); ?></label>
		<?php } else { ?>
			<label for="allow-all"><input id="allow-all" type="radio" name="tags" value="all" onclick="allselections();" /><?php echo JText::_( 'All' ); ?></label>
			<label for="allow-none"><input id="allow-none" type="radio" name="tags" value="none" onclick="disableselections();" /><?php echo JText::_( 'None' ); ?></label>
			<label for="allow-select"><input id="allow-select" type="radio" name="tags" value="select" onclick="enableselections();" checked="checked" /><?php echo JText::_( 'Select From List' ); ?></label>
			<?php } ?>
</td>
</tr>
<tr>
<td valign="top">
<?php echo JText::_( 'ALLOWABLE_TAGS_SELECTIONS' ); ?>:
</td>
<td>
<?php echo $this->lists['allowabletags']; ?>
</td>
</tr>
</table>
<?php if (($this->row->allowabletags == 'all')|| !($this->row->id)) { ?>
<script type="text/javascript">allselections();</script>
<?php } elseif ($this->row->allowabletags == 'none') { ?>
<script type="text/javascript">disableselections();</script>
<?php } else { ?>
<?php } ?>
</fieldset>  
 <br/>
<?php 
    echo $pane->endPanel();
    echo $pane->startPanel(JText::_("AUTHENTICATION"),"authdata");
?>
<fieldset class="adminform">
	<legend><?php echo JText::_("AUTHENTICATED_FEEDS");echo JHTML::tooltip(JText::_('AUTHENTICATION_TOOLTIP'), JText::_("AUTHENTICATION"), 'tooltip.png', '', '', false);?>
    </legend>
	<table>
	<thead><tr><th colspan="2"></th></tr></thead> 
  <tr> 
   <th style="width: 160px"><?php echo JText::_("Username")?>:</th>
   <td colspan="3"><input type="text" size="50" maxlength="50" name="u_field" value="<?php echo $this->row->username ?>"/>
   </td>
  </tr>
  <tr >
   <th style="width: 160px"><?php echo JText::_("Password")?>:</th>
   <td  colspan="3">
    <input type="password" name="p_field" size="30" maxlength="30" value="<?php  echo $this->row->password?>"/>
   </td>
  </tr>
  </table>
  </fieldset>
   
  <br/> 
<?php 
 echo $pane->endPanel();
 echo $pane->startPanel("<span onclick='showEngineOpts({$this->row->id})'>".JText::_("STORAGE_SETTINGS")."</span>","storeOpts");
?>
<fieldset class="adminform">
   <legend><?php echo JText::_( 'STORAGE_CONFIGURATION_OPTIONS' ); ?></legend>
 <script type="text/javascript" language="javascript">
 <!--
  var sectioncategories=new Array();
 //-->
 </script>
 <div id="storeOptionsDiv">
  <?php
        $engname=$this->row->storage;
        $engine=basename($engname,".engine.php");
        require_once(JPATH_COMPONENT_ADMINISTRATOR.DS."engines".DS.$engine.".engine.php");
        $engineName=$engine."F2pEng";
        if (class_exists($engineName,false)) {
            $cls=new $engineName();
            $data=$cls->showEngineSettings($this->row);
            echo $data;
        } else {
            echo "<b>Failed Engine</b>";
        }
 
  ?>
 </div>
 </fieldset>
<?php
 echo $pane->endPanel();
 echo $pane->endPane();
?>
   <input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
   <input type="hidden" name="cid[]" value="<?php echo $this->row->id; ?>" />
   <input type="hidden" name="mask" value="0" />
   <input type="hidden" name="option" value="<?php echo $this->option;?>" />
   <input type="hidden" name="task" value="" />
   <?php echo JHTML::_( 'form.token' ); ?>
  </form>
  <br/>
 <div  align="center">
  <a href="http://www.feed2post.com"><?php JText::_("FEED2POST_HOME_SITE")?></a>
 </div>