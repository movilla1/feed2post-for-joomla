<?php
/**
 * Feed2post v3.0
 * @author Mario O. Villarroel
 * @copyright 2010 - Elcan Software
 * @license GPLv2
 */
defined('_JEXEC') or die('Restricted access');

$document = &JFactory::getDocument();
$document->addStyleSheet(JURI::base().'components/com_feed2post/css/backend.css');
?>
<script language="javascript" type="text/javascript">
<!--
var sectioncategories = new Array;
<?php
$i = 0;
foreach ($this->sectioncategories as $k=>$items) {
  foreach ($items as $v) {
   echo "sectioncategories[".$i++."] = new Array( '$k','".addslashes( $v->id )."','".addslashes( $v->title )."' );\n\t\t";
  }
}
?>
-->
</script>
<form action="index.php?option=com_feed2post" method="post" name="adminForm" id="adminForm">
 <table  class="adminheading">
   <tr >
     <th class="title">
    	Feeds 2 post: <?php  echo JText::_("Import_Newsfeeds")?>
     </th>
   </tr>
  </table>
  <table align="center" class="adminlist">  
  <tr>
   <th class="F2P-form-label" width="5"><input type="checkbox" id='allcheck' name="allcheck" value="All" onclick='selectAll()' /></th> 
   <th class="F2P-form-label"><?php echo JText::_("Name")?></th> <th class='title'><?php echo JText::_("Address")?></th> 
  </tr>
<?php 
  foreach ($rows as $row) {
?>
     <tr>
      <td>
	<input type="checkbox" name="cid[]" value="<?php echo $row->id ?>"/>
      </td>
      <td ><?php  echo $row->name;?></td>
      <td ><?php  echo $row->link;?></td>
     </tr>		
<?php
   }
?>
</table> <br /><br />
<table class="adminheading">
 <tr >
  <th>Feed2post: <?php echo JText::_("Attributes for imported items")?></th>
 </tr>
</table>
<table class="adminlist">
  <tr> 
   <td class="F2P-form-label"><?php echo JText::_("Auto Unpublish")?></td> 
   <td colspan="2"> <?php  echo $this->lists["autounpub"] ?></td>
  </tr> 
  <tr> 
   <td class="F2P-form-label"><?php echo JText::_("Front Page")?></td> 
   <td colspan="2"> <?php echo $this->lists['frontpage'] ?></td>
  </tr> 
  <tr> 
   <td class="F2P-form-label"><?php echo JText::_("FULL_TEXT")?> <span style="font-size: 8px">(<?php echo JText::_("IF_FEED_FULL")?>)</span></td> 
   <td colspan="2"> <?php echo $this->lists['fulltext'] ?></td>
  </tr> 
  
  <tr>
  	<td class="F2P-form-label"><?php echo JText::_("Advertising")?>:</td> 
  	<td colspan="2"><textarea name="advert" id="advert" cols="50" rows="5"></textarea>
  </tr>
  <tr >
   <td class="F2P-form-label"><?php echo JText::_("VALID_FOR")?>:</td>
   <td  colspan="2"><?php echo $this->lists['validfor'] ?> </td>
  </tr>
  <tr >
   <td class="F2P-form-label"><?php echo JText::_("Section")?>:</td>
   <td colspan="2">
     <?php 
      	echo $this->lists['sectionid'];
     ?>
    </td> 
  </tr> 
  <tr> 
  <td class="F2P-form-label"><?php echo JText::_("Category")?>:</td>
  <td  colspan="2">
   <?php echo $this->lists['catid']; ?>
  </td>
  </tr> 
  </table>
   <input type="hidden" name="id" value="<?php echo $row->id; ?>" />
   <input type="hidden" name="mask" value="0" />
   <input type="hidden" name="task" value="" />
   <?php echo JHTML::_( 'form.token' ); ?>
  </form>
  <br/>
 <div  align="center">
  <a href="http://www.feed2post.com"><?php JText::_("Feed2Post_home_site")?></a>
 </div>
 <script type="text/javascript">
<!--
 function selectAll() {
   var form=document.forms[0];
   var ref=document.getElementById('allcheck').checked;
   for (i=0;i<form.elements.length;i++) {
     if (form.elements[i].type=='checkbox' && form.elements[i].name=='cid[]') {
       form.elements[i].checked=ref;
     }
   }
 }
-->
</script>