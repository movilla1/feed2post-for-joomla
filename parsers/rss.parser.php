<?php
/**
 * rss source parser for feed2post v3.0
 * @author Mario O. Villarroel
 * @copyright 2010 - Elcan Software
 * @license GPLv1
 * @abstract this parser will process rss entries and return the title, text and images for f2p to use
 */
defined('_JEXEC') or die('Restricted access');
//ini_set("display_errors","on");
require_once (JPATH_COMPONENT_ADMINISTRATOR . DS . "parsers" . DS . 'F2pParsers.php');
require_once (JPATH_COMPONENT_ADMINISTRATOR . DS . "parsers" . DS . "rss_utils.inc");
define("MAGPIE_DIR",JPATH_COMPONENT_ADMINISTRATOR.DS."parsers".DS);
require_once( JPATH_COMPONENT_ADMINISTRATOR.DS."parsers".DS. 'rss_parse.inc' );
require_once( JPATH_COMPONENT_ADMINISTRATOR.DS."parsers".DS. 'rss_cache.inc' );

class rssF2pParser extends F2pParser
{
    function getOptions() {
        return "<b>No options to set</b>";
    }
    
    function setOptions() {
        return true;
    }
    
    function getPannelName() {
        return JText::_("RSS Parser Options");
    }
    
    function getItems($row, $config)
    {
        $indexRetrn=0;
        parent::getItems($row, $config);
        define ("MAGPIE_CACHE_ON",false);
        define('MAGPIE_CACHE_DIR', JPATH_CACHE); // caching on the joomla cache folder.
        define('MAGPIE_OUTPUT_ENCODING', 'UTF-8'); //default for j.1.5
        define('MAGPIE_DETECT_ENCODING', true);
        define('MAGPIE_INPUT_ENCODING', null);
        //require_once (JPATH_COMPONENT_ADMINISTRATOR . DS . 'parsers' . DS .'rss_fetch.inc');
        $urllines = split("\n", chop(trim($this->srcUrl)));
        $allowabletags=$row->allowabletags;
        $max_requested=$row->maxitems;
        foreach ($urllines as $url) {
            $dataXML = $this->fetchWithCurl(html_entity_decode($url),$row->user,$row->password,($row->password."x"!="x"));
            if ($dataXML==false) continue; //error on the feed fetch, continue with the next one.
            $rss = new MagpieRSS( $dataXML, MAGPIE_OUTPUT_ENCODING, MAGPIE_INPUT_ENCODING, MAGPIE_DETECT_ENCODING );
            /*print_r($rss);
            exit();*/
            $item_count=0;
            $this->title = (isset($rss->channel['title'])) ? $rss->channel['title'] :
                "Unknown/Not_Set";
            if (count($rss->items) > 0) {
                foreach ($rss->items as $item) {
                    if ($max_requested!=0 && $item_count>$max_requested) {
                        break;
                    }
                    //act over fetched items.
                    $item_title = trim($item['title']);
                    $item_link = JRoute::_($item['link']);
                    if (strlen($item_link)<1 && isset($item['link_'])) $item_link=$item['link_']; 
                    if (isset($item['dc']['date'])) {
                        $secs=parse_w3cdtf($item['dc']['date']);
                        if ($secs==-1) {
                            $item_date=$item['dc']['date']; //if it can't be parsed, just use it
                        } else {
                            $item_date = date("Y-m-d H:i:s", $secs);
                        }
                    } elseif (isset($item['pubdate'])) {
                            $item_date = date("Y-m-d H:i:s", strtotime($item['pubdate']));
                    } elseif (isset($item['updated'])) {
                            $item_date = $this->parseAtomDate($item['updated']);
                    } else {
                            $item_date = date("Y-m-d H:i:s"); // if nothing is set, use current date and time.
                    }
                    
                    if ($allowabletags == "none") {
                        $item_text = strip_tags($this->parselinks($item['description']));
                        if (strlen($item_text)<1 && isset($item["summary"])) { //new for atom support
                            $item_text=$this->parselinks($item['summary']);
                        }
                        $item_full = (isset($item['content']['encoded'])) ? strip_tags($this->parselinks($item['content']['encoded'])) :
                            "";
                        if (strlen($item_full)<1 && isset($item['atom_content'])) $item_full=strip_tags($this->parselinks($item['atom_content']));    
                    } else {
                        if ($allowabletags == "all") {
                            $item_text = $this->parselinks($item['description']);
                            if (strlen($item_text)<1 && isset($item["summary"])) { //new for atom support
                                $item_text=$this->parselinks($item['summary']);
                            }
                            $item_full = (isset($item['content']['encoded'])) ? $this->parselinks($item['content']['encoded']) :
                                "";
                            if (strlen($item_full)<1 && isset($item['atom_content'])) $item_full=$this->parselinks($item['atom_content']);    
                        } else {
                            $item_text = strip_tags($this->parselinks($item['description']), $allowabletags);
                            if (strlen($item_text)<1 && isset($item["summary"])) { //new for atom support
                                $item_text=strip_tags($this->parselinks($item['summary']),$allowabletags);
                            }
                            $item_full = (isset($item['content']['encoded'])) ? strip_tags($this->parselinks($item['content']['encoded']), $allowabletags) : "";
                            if (strlen($item_full)<1 && isset($item['atom_content'])) $item_full=strip_tags($this->parselinks($item['atom_content']),$allowabletags);
                        }
                    }
                    $item_guid = (isset($item['guid'])) ? $item['guid'] : $item['id'];

                    if (strlen($item_guid) <= 1) {
                        $item_guid = md5($item_title); // if the item has no id and no guid, use a hash of the title.
                    }
                    if (isset($item['author'])) { 
                        $item_author = $item['author'];     
                    } elseif (isset($item['author_name'])) {
                        $item_author = $item['author_name'];
                    } else {
                        $item_author="N/A";
                    }
                    $item_images=(!is_null($this->parsed_imgs)) ? $this->parsed_imgs:null;
                    $urlsite=JURI::getInstance();
                    if (isset($item['enclosure'])) {
                        foreach ($item['enclosure'] as $enclose) {
                            if (stristr($enclose['type'],"image")!==false || stristr($enclose['type'],"img")!==false) {//found an image
                              $image=(isset($enclose['url'])) ? $enclose['url']:$enclose['href'];
                              if ($row->replaceimgs !=0 ) {
                                $this->insertImageIntoQueue($image);
                                $name=$this->getUniqueName($image);
                                $item_images[] = $urlsite->root()."index.php?option=com_feed2post&amp;task=getImage&amp;url=$name&amp;tmpl=component&amp;format=raw";
                              } else {
                                $item_images[] = $image;
                              }
                            }
                        }
                    }
                    $ignoreitem=$row->ignoreitem;
                    $cutAt=$row->cutat; 
                    $cutAtCharacter=$row->cutatcharacter;
                    $minimum_count=$row->minimum_count;
                    $truncate=$row->truncate;
                    $introT =strip_tags($item_text);
                    $item_text = $this->parseImagesSrc($item_text,$row->replaceimgs);
                    $item_full = $this->parseImagesSrc($item_full,$row->replaceimgs);

					if (($ignoreitem==0) || (($ignoreitem==1) && (strlen($introT)>$minimum_count)) || 
                        (($ignoreitem==2) && (strlen($item_text)>$minimum_count))){
						if ($cutAtCharacter > 0) {

								$item  = $this->truncate($item_text,0,$cutAtCharacter, '...', false, true); 
								$text_full = ($truncate==1)? "":rawurlencode($item_text). $item_full;
								$text_item = rawurlencode($item);	
									
						}else{ 
							if (strlen($cutAt) > 0) {
								$pos = strpos($item_text, $cutAt, 0);
								$item = substr($item_text, 0, $pos);
								$text_full = ($truncate==1) ? "":rawurlencode(substr($item_text, $pos + strlen($cutAt)));
								$text_item = rawurlencode($item);
							} else {
								$text_item = rawurlencode($item_text);
								$text_full = rawurlencode($item_full);
                			}
               			}
                        $searchFrom=$item_text . " " . $item_title. " ".$item_full;
                    
                        if (preg_match($this->keywords, $searchFrom)) {
                            //matching items
                            if (strlen(trim($this->negKeywords)) > 0 && !preg_match($this->negKeywords, $searchFrom)) {
                                $return_str[$indexRetrn] = rawurlencode(json_encode(array('title' => $item_title,
                                    "description" => $text_item, "content" => $text_full, "date" => $item_date,
                                    "link" => $item_link, "guid" => $item_guid, "author" => $item_author, "images" =>
                                    $item_images)));
                                $indexRetrn++;
                                $item_count++;
                            } else {
                                if (strlen(trim($this->negKeywords)) <= 0) {
                                    $return_str[$indexRetrn] = rawurlencode(json_encode(array('title' => $item_title,
                                        "description" => $text_item, "content" => $text_full, "date" => $item_date,
                                        "link" => $item_link, "guid" => $item_guid, "author" => $item_author, "images" =>
                                        $item_images)));
                                    $indexRetrn++;
                                    $item_count++;
                                }
                            }
                        } //end preg_match keywords
                    } //end if ignore_item
                } //end foreach item
            } // end if count
        } // end foreach url
        return $return_str;
    }
    /**
     * @abstract This function will parse atom date and time format and return a string for f2p to use
     * @param strdate is the atom valid string that it's set on "updated" field or similar, it must
     *        conform with RFC4287
     */
    function parseAtomDate($strdate) {
        defined ("TIME_BEGIN") or define("TIME_BEGIN",11);
       $datepart=substr($strdate,0,10);
       $lasttime=strpos($strdate,"Z");
       if ($lasttime==-1) {
         $lasttime=strpos($strdate,"+");
       } 
       $lasttime-=TIME_BEGIN;
       $timepart=substr($strdate,TIME_BEGIN,$lasttime); //got the time part.
       //we will drop the gmt offset
       $dateStr=$datepart." ".$timepart;
       return $dateStr; 
    }
    
} // end class

?>