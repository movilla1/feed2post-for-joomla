<?php
defined('_JEXEC') or die('Restricted access');
/**
 * Feed2post v3.0
 * @author Mario O. Villarroel
 * @copyright 2010 - Elcan Software
 * @license GPLv2
 */

$document = &JFactory::getDocument();
$document->addStyleSheet(JURI::base().'components/com_feed2post/css/backend.css');
?>
<br />
<form action="index.php?option=com_feed2post" method="post" name="adminForm" id="adminForm">
 <table  class="adminheading">
   <tr >
     <th class="F2P-form-section-title">
    	Feeds 2 post: <?php  echo JText::_('Defaults'); ?>
     </th>
   </tr>
  </table>
  <table align="center" class="adminform">  
  <tr >
   <th class="F2P-form-label"><?php echo JText::_("Published")?>:</th>
   <td >
   <?php echo $this->lists['published'];?>
   </td>
  	<th class="F2P-form-label"><?php echo JText::_("Advertising")?>:</th> 
  	<td colspan="3"><textarea name="advert" id="advert" cols="40" rows="4"><?php  echo $this->values->advert ?></textarea>
  	<?php echo JHTML::tooltip(JText::_('Advertising_Tooltip'), JText::_('Advertising'),'tooltip.png', '', '', false);?>
  	</td>
  </tr>
 <tr>
    <th class="F2P-form-label"><?php echo JText::_("Open readmore link at iframe")?>:</th>
    <td><?php echo $this->lists['readmorelink']; echo JHTML::tooltip(JText::_('Open_readmore_link_at_iframe_Tooltip'), JText::_("Open readmore link at iframe"), 'tooltip.png', '', '', false);?></td>
   <th class="F2P-form-label"><?php echo JText::_("Full text gather")?>:<br/><span style="font-size: 8px">(<?php echo JText::_("IF_FEED_FULL")?>)</span></th>
   <td><?php echo $this->lists['fulltext']; echo JHTML::tooltip(JText::_('Full_text_gather_Tooltip'), JText::_("FULL_TEXT"), 'tooltip.png', '', '', false);  ?></td>
  </tr>
  <tr>
   <th class="F2P-form-label"><?php echo JHTML::tooltip(JText::_("MAX_ITEMS_DESC"),JText::_("MAX_ITEMS"),"tooltip.png",JText::_("MAX_ITEMS")); ?>:</th>
   <td><input type="text" name="maxitems" value="<?php echo $this->values->maxitems?>" id="maxitems_inp"/></td>
   <th class="F2P-form-label"><?php echo JHTML::tooltip(JText::_('TRUNCATE_DESC'), JText::_("TRUNCATE"), '',JText::_("TRUNCATE"));?>:</th>
   <td><?php echo $this->lists['truncate']?></td>
  </tr>
    <tr>
    <th class="F2P-form-label"><?php echo JText::_("MINKEYWORD_LEN")?>:</th>
    <td><?php echo $this->lists['minkeylen']?></td>
    <th class="F2P-form-label"><?php echo JText::_("KEYWORD_COUNT")?>:</th>
    <td><?php echo $this->lists['keycount']?></td>
  </tr>
  <tr>
  <td class="F2P-form-section-title" colspan="4"><strong><?php echo JText::_( 'Iframe Configuration Options' ); ?></strong></th>
  <tr> 
    <th class="F2P-form-label"><?php echo JText::_("Width")?>:</th>
    <td><input type="text" value="<?php echo $this->values->width;?>" name="width" size="10" maxlength="8" /></td>
    <th class="F2P-form-label"><?php echo JText::_("Height")?>:</th>
    <td><input type="text" value="<?php echo $this->values->height;?>" name="height" size="10" maxlength="8" /></td>
  </tr>  
  <tr>
   <th class="F2P-form-label"><?php echo JText::_("Scrolling")?>:</th>
   <td><?php  echo $this->lists['scrolling'] ; ?></td>
   <th class="F2P-form-label"><?php echo JText::_("Frame border")?>:</th>
   <td><?php echo $this->lists['frameborder']?>:</td>
  </tr>  
   <tr>
    <th class="F2P-form-label"><?php echo JText::_("Margin Width")?>:</th>
    <td><input type="text" value="<?php echo $this->values->marginwidth;?>" name="marginwidth" size="10" maxlength="8" /></td>
    <th class="F2P-form-label"><?php echo JText::_("Margin Height")?>:</th>
    <td><input type="text" value="<?php echo $this->values->marginheight;?>" name="marginheight" size="10" maxlength="8" /></td>
  </tr> 
  <tr>
    <th class="F2P-form-label"><?php echo JText::_("Align")?>:</th>
    <td><?php echo $this->lists['align']; ?></td>
  </tr>
  </table>
   <input type="hidden" name="task" value="" />
   <?php echo JHTML::_( 'form.token' ); ?>
  </form>
 <br/> 
 <div  align="center">
  <a href="http://www.feed2post.com/"><?php echo JText::_("Feed2Post_home_site")?></a>
 </div>