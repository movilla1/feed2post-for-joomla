<?php
/**
 * Feed2post v3.0
 * @author Mario O. Villarroel
 * @copyright 2010 - Elcan Software
 * @license GPLv2
 */
defined('_JEXEC') or die('Restricted access');
?>
   <form action="index.php?option=com_feed2post" method="post" name="adminForm" id="adminForm">
   <table  class="adminheading">
   <tr >
     <th class="title">
    	<?php  echo $this->title ?>
     </th>
   </tr>
  </table>
  <table align="center" class="adminlist"> 
  <tr >
     <th class="title">
    	Feed2post, <?php echo JText::_("Feed_data_from"); echo ": ".$this->feed_url; ?>
     </th>
   </tr>
  </table>
   <table align="center" class="adminlist">
  <tr>
   <th class='title' width="5"><input type="checkbox" id='allcheck' name="allcheck" value="All" onclick='selectAll()' /></th> 
   <th class='title' width="5">#</th>
   <th class='title' width="40em"><?php echo JText::_("Title")?> </title> 
   <th class='title' width="25em"><?php echo JText::_("Date")?></th>
   <th class='title'><?php echo JText::_("Text ")?></th> 
  </tr>
<?php
	//process the returned text array here...
	for ($it=0;$it<count($this->items);$it++) {
	 $item=json_decode(rawurldecode($this->items[$it]));
?>
	<tr>
		<td>
			<input type="checkbox" name="cid[]" value="<?php echo $it ?>"/>
			<input type="hidden" name="contents[]" value="<?php echo $this->items[$it]?>"/>
		</td>
		<td><?php echo $it ?></td>
		<td><div id='article_full_f2p'><a  href="<?php echo rawurldecode($item->link); ?>" target="_blank"><?php echo rawurldecode($item->title);?></a></div></td>
		<td><?php echo rawurldecode($item->date)?></td>
		<td><?php echo rawurldecode($item->description)?></td>
	</tr>
<?php
	} 
?>
  </table> 
  <input type="hidden" name="id" value="<?php  echo $this->id ?>" />
  <input type="hidden" name="frontpage" value="<?php echo $this->frontpage ?>" />
  <input type="hidden" name="task" value="" />
  <?php echo JHTML::_( 'form.token' ); ?>
</form>
 <br/>
 <div  align="center">
  <a href="http://www.feed2post.com"><?php echo JText::_("Feed2Post_home_site")?></a>
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
//-->
</script>