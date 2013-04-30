<?php
defined('_JEXEC') or die('Restricted access');
/**
 * Feed2post v3.0
 * @author Mario O. Villarroel
 * @copyright 2010 - Elcan Software
 * @license GPLv2
 */
?>
<form action="index.php?option=com_feed2post" method="post" name="adminForm" id="adminForm">
<table class="adminlist">
 <thead>
 <tr>
  <th style="width: 20px">
   <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $rows ); ?>);" />
  </th>
  <th class="title" style="width: 150px"><a href="index.php?option=com_feed2post&task=list&ord=1"><?php echo JText::_("Title") ?></a></th>
  <th class="title" style="width: 30px"><?php  echo JText::_("Published") ?></th>
  <th class="title" style="width: 30px;"><?php echo JText::_("Username");?> / <?php echo JText::_("Password")?></th>
  <th style="width: 30px">
   <?php  echo JText::_("FULL_TEXT") ?>
  </th>
  <th style="width:15%"><a href="index.php?option=com_feed2post&task=list&ord=3"><?php echo JText::_("Keywords");?></a></th>
  <th style="width:15%"><?php echo JText::_("NEGATIVE_KEYWORDS");?></th>
  <th style="width:15%"><?php echo JText::_("Source Format") ?></th>
  <th style="width:15%"><?php echo JText::_("Storing as") ?></th>
 </tr>
 </thead>
<?php
   $k = 0;
   $n=count( $this->rows );
   for ($i=0; $i < $n; $i++) {
	$row = &$this->rows[$i];
	$checked = JHTML::_('grid.id', $i, $row->id );
	$img=($row->published==1||$row->published==2)? "publish_g.png" : "publish_x.png";
	$alt=($row->published==1||$row->published==2)? "Published":"Unpublished";
  ?>
  <tr class="<?php echo "row$k"; ?>">
   <td><?php echo $checked; ?></td>
   <td><a href="index.php?option=com_feed2post&task=edit&cid[]=<?php  echo $row->id;?>"><?php echo $row->title ?></a></td >
   <td style="text-align: center">	
      <span class="editlinktip hasTip" title="<?php echo JText::_( 'Auto-Publishing' );?>">
       <a href="javascript:void(0);" onclick="return listItemTask('cb<?php echo $i;?>','<?php echo $row->published ? 'unpublish' : 'publish' ?>')">
	   <img src="images/<?php echo $img;?>" width="16" height="16" border="0" alt="<?php echo $alt; ?>" /></a>
      </span>
   </td>
   <td><center><?php echo $row->username ?> / <?php echo str_pad("*",strlen(trim($row->password)))?></center></td>
   <td><?php echo $row->fulltext; ?></td>
   <td><?php echo $row->keywords; ?></td>
   <td><?php echo $row->negkey; ?></td>
   <td><?php echo strtoupper($row->parser); ?></td>
   <td><?php echo ucfirst($row->storage); ?></td>
  </tr>
   <?php
     $k = 1 - $k;
   }
  ?>
  <tfoot>
    <tr>
	<td colspan="10">
	<?php echo $this->page->getListFooter(); ?>
	</td>
    </tr>
  </tfoot>
  </table> 
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="boxchecked" value="0" />
 </form>
