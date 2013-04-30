<?php
//extra functions.
/**
 * Gets the lists to be shown on the f2p interface.
 */
function getListsGen($row)
{
    $db = &JFactory::getDBO();

    //$lists['authoralias'] = JHTML::_('select.booleanlist', 'authoralias'	, 'class="inputbox"', $row->authoralias);
    $lists['fulltext'] = JHTML::_('select.booleanlist', 'fulltext',
        'class="inputbox"', $row->fulltext);
    $lists['readmorelink'] = JHTML::_('select.booleanlist', 'iframelinks',
        'class="inputbox"', $row->iframelinks);
    /*$delays[] = JHTML::_('select.option', '2', JText::_('2_MIN'), 'id', 'title');
    $delays[] = JHTML::_('select.option', '10', JText::_('10_MIN'), 'id', 'title');
    $delays[] = JHTML::_('select.option', '20', JText::_('20_MIN'), 'id', 'title');
    $delays[] = JHTML::_('select.option', '30', JText::_('30_MIN'), 'id', 'title');
    $delays[] = JHTML::_('select.option', '60', JText::_('1_HOUR'), 'id', 'title');
    $delays[] = JHTML::_('select.option', '120', JText::_('2_HOURS'), 'id', 'title');*/
    $ignoreitems[] = JHTML::_('select.option', '0', JText::_('No'), 'value', 'text');
    $ignoreitems[] = JHTML::_('select.option', '1', JText::_('Count only text'),
        'value', 'text');
    $ignoreitems[] = JHTML::_('select.option', '2', JText::_('Count raw'), 'value',
        'text');
    $lists['ignoreitem'] = JHTML::_('select.genericlist', $ignoreitems, 'ignoreitem',
        'class="inputbox"', 'value', 'text', intval($row->ignoreitem));
    $lists['published'] = JHTML::_('select.booleanlist', 'published',
        'class="inputbox"', $row->published);
    $lists['frameborder'] = JHTML::_('select.booleanlist', 'frameborder','class="inputbox"', $row->frameborder);
    $scr[] = JHTML::_('select.option', 'no', JText::_('No'), 'id', 'title');
    $scr[] = JHTML::_('select.option', 'yes', JText::_('Yes'), 'id', 'title');
    $scr[] = JHTML::_('select.option', 'auto', JText::_('Auto'), 'id', 'title');
    $lists['scrolling'] = JHTML::_('select.genericlist', $scr, 'scrolling',
        'class="inputbox" size="1"', 'id', 'title', $row->scrolling);
    $alg[] = JHTML::_('select.option', 'left', JText::_('Left'), 'id', 'title');
    $alg[] = JHTML::_('select.option', 'right', JText::_('Right'), 'id', 'title');
    $alg[] = JHTML::_('select.option', 'top', JText::_('Top'), 'id', 'title');
    $alg[] = JHTML::_('select.option', 'middle', JText::_('Middle'), 'id', 'title');
    $alg[] = JHTML::_('select.option', 'bottom', JText::_('Bottom'), 'id', 'title');
    $lists['align'] = JHTML::_('select.genericlist', $alg, 'align','class="inputbox" size="1"', 'id', 'title', $row->align);
    $allowabletag[] = JHTML::_('select.option', '<a>', 'a', 'id', 'title');
    $allowabletag[] = JHTML::_('select.option', '<b>', 'b', 'id', 'title');
    $allowabletag[] = JHTML::_('select.option', '<br>', 'br', 'id', 'title');
    $allowabletag[] = JHTML::_('select.option', '<p>', 'p', 'id', 'title');
    $allowabletag[] = JHTML::_('select.option', '<img>', 'img', 'id', 'title');
    $allowabletag[] = JHTML::_('select.option', '<link>', 'link', 'id', 'title');
    $allowabletag[] = JHTML::_('select.option', '<strong>', 'strong', 'id', 'title');
    $allowabletag[] = JHTML::_('select.option', '<ul>', 'ul', 'id', 'title');
    $allowabletag[] = JHTML::_('select.option', '<li>', 'li', 'id', 'title');
    $allowabletag[] = JHTML::_('select.option', '<ol>', 'ol', 'id', 'title');
    $allowabletag[] = JHTML::_('select.option', '<table>', 'table', 'id', 'title');
    $allowabletag[] = JHTML::_('select.option', '<script>', 'script', 'id', 'title');
    $selectedTags = explode(" ", $row->allowabletags);
    $lists['allowabletags'] = JHTML::_('select.genericlist', $allowabletag,
        'allowabletags[]', 'class="inputbox" multiple="multiple" size="7"', 'id',
        'title', $selectedTags, $allowabletags);
    $lists['parsers'] = getParserList($row->parser);
    $lists['engines'] = getEnginesList($row->storage);
    $lists['includelink'] = JHTML::_('select.booleanlist','includelink','class="inputbox"',$row->includelink);
    $lists['replaceimgs'] = JHTML::_('select.booleanlist','replaceimgs','class="inputbox"',$row->replaceimgs);
    $lists['truncate'] = JHTML::_('select.booleanlist','truncate','class="inputbox"',$row->truncate);
    $lists['maxitems'] = "<input type='text' name='maxitems' value='{$row->maxitems}' id='maxitems_in'>";
    $lists['keycount']="<input type='text' name='keycount' value='{$row->keycount}' id='keycount_in'>";
    $lists['minkeylen']="<input type='text' name='minkeylen' value='{$row->minkeylen}' id='minkeylen_in'>";
    return $lists;
}

/**
 * This function gathers the parser names and makes a list for the user to select one.
 */
function getParserList($parserid)
{
    $mainframe=&JFactory::getApplication();
    $df = opendir(dirname(__file__) . "/parsers");
    if ($df) {
        while ($dat = readdir($df)) {
            if (strstr($dat, ".parser.php") !== false) {
                $parser[] = JHTML::_('select.option', basename($dat,".parser.php"), basename($dat,".parser.php"), 'name', 'id');
            }
        }
    } else {
        $parser[] = JHTML::_('select.option', JText::_('NO_PARSER'));
    }
    return JHTML::_('select.genericlist', $parser, 'parser', 'class="inputbox"',
        'name', 'id', $parserid);
}

/**
 * This function gathers the parser names and makes a list for the user to select one.
 */
function getEnginesList($engineid)
{
    $mainframe=&JFactory::getApplication();
    $df = opendir(dirname(__file__) . "/engines");
    if ($df) {
        while ($dat = readdir($df)) {
            if (strstr($dat, ".engine.php") !== false) {
                $engine[] = JHTML::_('select.option', basename($dat,".engine.php"), basename($dat,".engine.php"), 'name', 'id');
            }
        }
    } else {
        $engine[] = JHTML::_('select.option', JText::_('NO_STORAGE_ENGINE'));
    }
    return JHTML::_('select.genericlist', $engine, 'storage', 'onchange="enginechanged=true;return true" class="inputbox"',
        'name', 'id', $engineid);
}

function getDefaults()
{
    $tbl = &JTable::getInstance("Feed2postConfig");
    $tbl->load('2'); //get the defaults from the table config.
    $vals = (string )$tbl->values;
    $values = json_decode($vals);
    return $values;
}

function getConfig()
{
    $mainframe=&JFactory::getApplication();
    $row = &JTable::getInstance("Feed2postConfig");
    $row->load('1');
    $rets = json_decode(stripslashes($row->values));
    return $rets;
}

function is_16() {
    $version = new JVersion();
    $joomla = $version->getShortVersion();
    $ret=false;
    $ver=substr($joomla,0,3);
    if ($ver=="1.6" || $ver=="1.7" || $ver=="2.5")
            $ret=true;
    return $ret;
}

 function genKeyWords($text, $blackListWords, $count, $minLength) {
	  $text = strip_tags($text); 
	  $text = preg_replace('/<[^>]*„“”">/', ' ', $text);	
	  $text = preg_replace('/[\.;:|\'|\„“”"|\`|\,|\(|\)|\-]/', ' ', $text);	
	  $wordArray = explode(" ", $text);
	  $wordArray = array_count_values(array_map('strtolower', $wordArray));
	
  	$blackArray = explode(",", $blackListWords);
	
	  foreach($blackArray as $blackWord){
		  if(isset($wordArray[trim($blackWord)]))
			  unset($wordArray[trim($blackWord)]);
	  }
	
	  arsort($wordArray);
	
	  $i = 1;
	
	  foreach($wordArray as $word=>$instances){
		  if($i > $count)
			  break;
		  if(strlen(trim($word)) >= $minLengthkeys ) {
			  $keys .= $word . ", ";
			  $i++;
		  }
	  }
	
	  $keys = rtrim($keys, ", ");
	  return($keys);
  }	

?>