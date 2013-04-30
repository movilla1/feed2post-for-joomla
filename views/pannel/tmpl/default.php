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
 <form action="index.php?option=com_feed2post" method="post" name="adminForm" id="adminForm">
  <input type="hidden" name="task" value="" />
 </form>
 <table id="F2P-pannel">
 <tr>   
    <td><a href="index.php?option=<?php echo $this->option?>&task=itemList"><div class="F2P-icon-48-browser">&nbsp;</div><br /><?php echo JText::_("VIEW_ALL")?></a></td>
    <td><a href="index.php?option=<?php echo $this->option?>&task=showOptions"><div class="F2P-icon-48-config">&nbsp;</div><br /><?php echo JText::_("CONFIGURE_F2P")?></a></td>
    <td rowspan="4" style="width: 50%;"><iframe src="http://www.feed2post.com/f2pnews.php" class="F2P-help-frame" scrolling="auto" ></iframe></td>
 </tr>
 <tr>
    <td><a href="index.php?option=<?php echo $this->option?>&task=showDefaults"><div class="F2P-icon-48-cpanel">&nbsp;</div><br /><?php echo JText::_("DEFAULTS_F2P")?></a></td>
    <td><a href="http://www.feed2post.com/support/forum" target="_blank"><div class="F2P-icon-48-help_header">&nbsp;</div><br /><?php echo JText::_("get_help")?></a></td>
 </tr>
 <tr>
    <td><a href="index.php?option=com_content"><div class="F2P-icon-48-article">&nbsp;</div><br /><?php echo JText::_("article_manager")?></a></td>
    <?php if (!is_16()) {?><td style="width: 25%"><a href="index.php?option=com_sections&scope=content"><div class="F2P-icon-48-sections">&nbsp;</div><br /><?php echo JText::_("sections_manager")?></a></td>
 </tr>
 <tr><?php } ?>
    <td><a href="index.php?option=com_categories&section=com_content"><div class="F2P-icon-48-categories">&nbsp;</div><br /><?php echo JText::_("category_manager")?></a></td>
 </tr>
 </table>
