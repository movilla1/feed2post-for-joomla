<?php
defined('_JEXEC') or die('Restricted access');
/**
 * Feed2post v3.0
 * @author Mario O. Villarroel
 * @copyright 2010 - Elcan Software
 * @license GPLv2
 */
jimport('joomla.html.pane');

$document = &JFactory::getDocument();
$document->addStyleSheet(JURI::base().'components/com_feed2post/css/backend.css');
?>
<form action="index.php?option=com_feed2post" method="post" name="adminForm" id="adminForm">
<?php
$pane =& JPane::getInstance('tabs', array('startOffset'=>0));
echo $pane->startPane( 'pane' );
echo $pane->startPanel( JText::_('General Settings'), 'panel1' ); 
?>
  <table align="center" class="adminlist">  
  <!--<tr >
    <th class="F2P-form-label"><?php //echo JText::_("Get With CURL")?>:</th>
    <td><?php  //echo $this->lists['getwith'] ?> </td>
  </tr>-->
  <tr >
    <th class="F2P-form-label"><?php echo JText::_("Insert Advertising")?>:</th>
    <td><fieldset id="jform_type" class="radio inputbox"><?php  echo $this->lists['insertadvert'] ?></fieldset></td>
  </tr>
  <tr >
    <th class="F2P-form-label"><?php echo JText::_("Include link to original")?>:</th>
    <td><fieldset id="jform_type" class="radio inputbox"><?php  echo $this->lists['includelink'] ?></fieldset></td>
  </tr>
    <tr >
    <th class="F2P-form-label"><?php echo JHTML::tooltip(JTEXT::_("Warning_Dup_Seo"),JText::_("Duplicate avoidance"),"",JText::_("Duplicate avoidance"));?>:</th>
    <td><?php  echo $this->lists['avoiddup'] ?> </td>
  </tr>
  <tr>
   <th><?php echo JHTML::tooltip(JTEXT::_("SEO_BAD_WORDS_DESC"),JText::_("SEO_BAD_WORDS"),"",JText::_("BAD_WORDS"));?>:</th>
   <td><textarea name="badwords" cols="40" rows="5"><?php echo $this->badwords ?></textarea></td>
  </tr>
  </table> 
   <input type="hidden" name="task" value="" />
   <?php echo JHTML::_( 'form.token' ); ?>
 <br/>
 <!--<font size="-2">** <?php //echo JText::_("")?></font>-->
 <br/> 
<?php 
    echo $pane->endPanel();
    $x=1;
    foreach ($this->plugins as $plugin) {
        $parsername=$plugin."F2pParser";
        require_once(JPATH_COMPONENT_ADMINISTRATOR.DS."parsers".DS.$plugin.".parser.php");
        $plg = new $parsername;
        echo $pane->startPanel($plg->getPannelName(),'plg'.$x++);
        $str_opts=$this->opts[$plugin]['values'];
        if (isset($this->opts[$plugin])) $plg->setOptions($str_opts);
        echo $plg->getOptions();
        echo $pane->endPanel();
    }
    echo $pane->endPane();
?>
  </form>
 <div  align="center">
  <a href="http://www.feed2post.com/"><?php echo JText::_("Feed2Post_home_site")?></a>
 </div>