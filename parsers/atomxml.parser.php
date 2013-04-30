<?php
defined ("_JEXEC") or die("No entry point, please use the proper init");

/**
 * rss source parser for feed2post v3.0
 * @author Mario O. Villarroel
 * @copyright 2010 - Elcan Software
 * @license (c) 2012 - Elcan Software
 * @abstract this parser will process Sports Direct Inc. News entries and return the title, text and images for f2p to use
 */
require_once (JPATH_COMPONENT_ADMINISTRATOR . DS . "parsers". DS .'F2pParsers.php');

class atomxmlF2pParser extends F2pParser {
    var $basepath;
    function getOptions() {
        $ret="<h2>Sports Direct Inc. Parser</h2>".JText::_("ASSET_BASEPATH_STR")."<br/><br/>";
        $ret.="<label for='base'>".JText::_("ASSET_BASEPATH")."</label><input type='text' name='parseroption[sp_basepath]' value='".$this->basepath."'/><br/>";
        return $ret;
    }
    function setOptions($json_string) {
        $opt=json_decode($json_string,true);
        if (isset($opt['sp_basepath'])) $this->basepath=$opt['sp_basepath']; else $this->basepath=JPATH_ROOT.DS."media";
        return true;
    }
    function getPannelName() {
        return JText::_("Sports Direct Inc. Parser Options");
    }
    
    function getItems($row, $config) {
        //here goes the code to get the items and text
        $indexRetrn=0;
        parent::getItems($row, $config);
        define('MAGPIE_CACHE_DIR', JPATH_CACHE); // caching on the joomla cache folder.
        define('MAGPIE_OUTPUT_ENCODING', 'UTF-8'); //default for j.1.5
        require_once (JPATH_COMPONENT_ADMINISTRATOR . DS . 'parsers' . DS . 'rss_fetch.inc');
        require_once (JPATH_COMPONENT_ADMINISTRATOR . DS . "parsers" . DS . "rss_utils.inc");
        $urllines = split("\n", chop(trim($this->srcUrl)));
        $allowabletags=$row->allowabletags;
        foreach ($urllines as $url) {
            $rss = fetch_rss(html_entity_decode($url));
            if (count($rss->items) > 0) {
                foreach ($rss->items as $item) {
                    $article_href=$item['link_']; //it's going to work only for atom links
                    //echo "$article_href\n";
                    $fetched=F2pParser::fetchWithCurl($article_href);
                    if (strlen($fetched)>1) 
                      $xml=simplexml_load_string($fetched,'SimpleXMLElement', LIBXML_NOCDATA);
                    else
                      continue;
                    $article=$xml->{'article-content'}->article;
                    $item_title=(string)$article->title;
                    $body=(string)$article->body;
                    $item_full=$item_text=$body;
                    if ($allowabletags == "none") {
                        $item_full=strip_tags($item_full);
                        $item_text=strip_tags($item_text);
                    } else if ($allowabletags=="all") {
                        $item_full=$this->parselinks($item_full);
                        $item_text=$this->parselinks($item_text);
                    } else {
                        $item_full=$this->parselinks(strip_tags($item_full,$allowabletags));
                        $item_text=$this->parselinks(strip_tags($item_text,$allowabletags));
                    }
                    $item_text=mb_substr($item_text,0,180); //keep only 180 characters as intro.
                    $item_date=$this->parsedate($article->date);
                    $item_author=(string)$article->author->name;
                    $item_guid=(string)$article->id;
                    $item_link="";
                    $item_images="";
                    $ignoreitem=$row->ignoreitem;
                    $cutAt=$row->cutat; 
                    $cutAtCharacter=$row->cutatcharacter;
                    $minimum_count=$row->minimum_count;
                    $introT =strip_tags($item_text);
					if (($ignoreitem==0) || (($ignoreitem==1) && (strlen($introT)>$minimum_count)) || 
                        (($ignoreitem==2) && (strlen($item_text)>$minimum_count))){
						if ($cutAtCharacter > 0) {

								$item  = $this->truncate($item_text,0,$cutAtCharacter, '...', false, true); 
								$text_full = rawurlencode($item_text). $item_full;
								$text_item = rawurlencode($item);	
									
						}else{ 
							if (strlen($cutAt) > 0) {
								$pos = strpos($item_text, $cutAt, 0);
								$item = substr($item_text, 0, $pos);
								$text_full = rawurlencode(substr($item_text, $pos + strlen($cutAt)));
								$text_item = rawurlencode($item);
							} else {
								$text_item = rawurlencode($item_text);
								$text_full = rawurlencode($item_full);
                			}
               			}
                        $searchFrom=$item_full . " " . $item_title; //text and full are the same, text is shorter so we discard it.
                        if (preg_match($this->keywords, $searchFrom)) {
                            if (strlen(trim($this->negKeywords)) > 0 && !preg_match($this->negKeywords, $searchFrom)) {
                                $return_str[$indexRetrn] = rawurlencode(json_encode(array('title' => $item_title,
                                    "description" => $text_item, "content" => $text_full, "date" => $item_date,
                                    "link" => $item_link, "guid" => $item_guid, "author" => $item_author, "images" =>
                                    $item_images)));
                                $indexRetrn++;
                            } else
                                if (strlen(trim($this->negKeywords)) <= 0) {
                                    $return_str[$indexRetrn] = rawurlencode(json_encode(array('title' => $item_title,
                                        "description" => $text_item, "content" => $text_full, "date" => $item_date,
                                        "link" => $item_link, "guid" => $item_guid, "author" => $item_author, "images" =>
                                        $item_images)));
                                    $indexRetrn++;
                                }
                        }
                    } //end ignoreitem 
                } //end foreach $item
            } //end if count(items)>0
        } //end foreach url
        return $return_str;
    }
    
    function parsedate($datestr) {
        $isodate=substr($datestr,0,10);
        $time=substr($datestr,11,10);
        $offst=substr($datestr,23);
        return $isodate." ".substr($time,0,8);
    }
}