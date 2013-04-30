<?php
/**
 * Base parser class, shows the interface and basic i/o functions.
 * @author Mario O. Villarroel
 * @copyright 2010 - Elcan Software
 * @license GPLv1
 * @abstract This base class has the interface elements for f2p to use and the base i/o mechs.
 */
defined('_JEXEC') or die('Restricted access');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS."library".DS."simple_html_dom.php");

class F2pParser
{
    var $f2prow;
    var $keywords;
    var $negKeywords;
    var $config;
    var $srcUrl;
    var $title;
    var $http_code;
    var $parsed_imgs;
    /**
     * This function gets the items from the src specified in the row url.
     * It returns an array with a Json encoded string per index
     * Must be implemented in each derivative class
     */
    function getItems($row, $config)
    {

        if (!empty($row->password)) {
            /*$urls = "";
            $urllines = split("\n", chop(trim($row->feed_url)));
            foreach ($urllines as $url) {
                $urlparts = parse_url($url);
                $new_url = $urlparts['scheme'] . "://" . $row->username . ":" . $row->password .
                    "@" . $urlparts['host'];
                if (!empty($urlparts['port']))
                    $new_url .= ":" . $urlparts['port'];
                $new_url .= (isset($urlparts['path'])) ? "/" . $urlparts['path']:"";
                if (isset($urlparts['query']))
                    $new_url .= "?" . $urlparts['query'];
                $urls .= $new_url . "\n";
            }
            $this->srcUrl = $urls;*/
            //print_r($urls);
            $this->srcUrl = $row->feed_url;
            echo "Authenticated Feed, using username:".$row->username." and password:".preg_replace("/.*/","****...",$row->password);
        } else {
            $this->srcUrl = $row->feed_url;
        }
        if (strlen(trim($row->keywords))>0) {
            $kyw = split(",", $row->keywords);
            $kyw = array_map("trim",$kyw);
            $kwds = implode("|", $kyw);
            $this->keywords = "/$kwds/i";
        } else {
            $this->keywords="/.*/";
        }
        if (strlen($row->negkey) > 0) {
            $neg = split(",", $row->negkey);
            $neg = array_map("trim",$neg);
            $nkeywords = implode("|", $neg);
            $this->negKeywords = "/" . $nkeywords . "/i";
        } else {
            $this->negKeywords = "";
        }
        $this->row = $row;
        $this->config = $config;
    }

    function getTitle()
    {
        return $this->title;
    }
    /**
     * @abstract this function parses the src on images and iframes, if they are relative, it makes them absolute
     *           In order to avoid images or script loading problems.
     * @param text with the article content.
     * @param replace boolean to allow the image replacing
     */
    function parseImagesSrc($text,$replace=false) {
        require(JPATH_COMPONENT_ADMINISTRATOR.DS."defines.php");
        //print_r($text);
        $dom=str_get_html($text);
        $urlsite=JURI::getInstance();
        if (is_object($dom)) {
            $db=& JFactory::getDBO();
            $count=0;
            if (!is_null($this->parsed_imgs)) unset($this->parsed_imgs);
            $this->parsed_imgs=array();
            foreach($dom->find("img") as $elem) {
                $url=$elem->src;
                $this->parsed_imgs[]=$url;
                if ($replace!=0) {
                   $name=F2pParser::getUniqueName($url);
                   $elem->outertext="<!--startIMG $name --><img src='".$urlsite->root()."index.php?option=com_feed2post&amp;task=getImage&amp;url=$name&amp;tmpl=component&amp;format=raw' alt=''><!--endIMG-->";
                  /* print_r($elem);
                   exit();*/
                   F2pParser::insertImageIntoQueue($url);
                }
            }
            $text=$dom->save();
        } 
        return $text;
    } 
    
    function parselinks($datatext)
    {
        /*$text = preg_replace("/<a\s+/", "<a target=\"_blank\" rel=\"nofollow\" ", $datatext);
        return $text;*/
        $dom=str_get_html($datatext);
        if (is_object($dom)) {
            foreach ($dom->find("a") as $elem) {
                $elem->target="_blank";
                $elem->rel="nofollow";
            }
            $text=$dom->save();
        } else {
            $text=$datatext; //return the same text if the parsing was not done...
        }
        return $text;
    }
    /**
     * Truncates text.
     *
     * Cuts a string to the length of $length and replaces the last characters
     * with the ending if the text is longer than length.
     *
     * @param string  $text String to truncate.
     * @param integer $length Length of returned string, including ellipsis.
     * @param mixed $ending If string, will be used as Ending and appended to the trimmed string. Can also be an associative array that can contain the last three params of this method.
     * @param boolean $exact If false, $text will not be cut mid-word
     * @param boolean $considerHtml If true, HTML tags would be handled correctly
     * @return string Trimmed string.
     */
    function truncate($text, $start, $length = 100, $ending = '...', $exact = true,
        $considerHtml = false)
    {
        if (is_array($ending)) {
            extract($ending);
        }
        if ($considerHtml) {
            if (mb_strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
                return $text;
            }
            $totalLength = mb_strlen($ending);
            $openTags = array();
            $truncate = '';
            preg_match_all('/(<\/?([\w+]+)[^>]*>)?([^<>]*)/', $text, $tags, PREG_SET_ORDER);
            foreach ($tags as $tag) {
                if (!preg_match('/img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param/s',
                    $tag[2])) {
                    if (preg_match('/<[\w]+[^>]*>/s', $tag[0])) {
                        array_unshift($openTags, $tag[2]);
                    } else
                        if (preg_match('/<\/([\w]+)[^>]*>/s', $tag[0], $closeTag)) {
                            $pos = array_search($closeTag[1], $openTags);
                            if ($pos !== false) {
                                array_splice($openTags, $pos, 1);
                            }
                        }
                }
                $truncate .= $tag[1];

                $contentLength = mb_strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i',
                    ' ', $tag[3]));
                if ($contentLength + $totalLength > $length) {
                    $left = $length - $totalLength;
                    $entitiesLength = 0;
                    if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $tag[3], $entities,
                        PREG_OFFSET_CAPTURE)) {
                        foreach ($entities[0] as $entity) {
                            if ($entity[1] + 1 - $entitiesLength <= $left) {
                                $left--;
                                $entitiesLength += mb_strlen($entity[0]);
                            } else {
                                break;
                            }
                        }
                    }

                    $truncate .= mb_substr($tag[3], $start, $left + $entitiesLength);
                    break;
                } else {
                    $truncate .= $tag[3];
                    $totalLength += $contentLength;
                }
                if ($totalLength >= $length) {
                    break;
                }
            }

        } else {
            if (mb_strlen($text) <= $length) {
                return $text;
            } else {
                $truncate = mb_substr($text, $start, $length - strlen($ending));
            }
        }
        if (!$exact) {
            $spacepos = mb_strrpos($truncate, ' ');
            if (isset($spacepos)) {
                if ($considerHtml) {
                    $bits = mb_substr($truncate, $spacepos);
                    preg_match_all('/<\/([a-z]+)>/', $bits, $droppedTags, PREG_SET_ORDER);
                    if (!empty($droppedTags)) {
                        foreach ($droppedTags as $closingTag) {
                            if (!in_array($closingTag[1], $openTags)) {
                                array_unshift($openTags, $closingTag[1]);
                            }
                        }
                    }
                }
                $truncate = mb_substr($truncate, $start, $spacepos);
            }
        }

        $truncate .= $ending;

        if ($considerHtml) {
            foreach ($openTags as $tag) {
                //$truncate .= '';
                $truncate .= "</$tag>";
            }
        }

        return $truncate;
    }
    /**
     * This function will show the options to put in a dialog.
     * Options here will set stuff for the parser to work, e.g. twitter oauth data...
     */
    function getOptions() {
        return true;
    }
    /**
     * This function is to set parser specific options
     * @param $json_string is a json encoded string, it will hold all the
     *        parser specific options values.
     */
    function setOptions($json_string) {
        return true;
    }
    
    function getPannelName() {
        return "Empty";
    }
    
    static function getParserOptions($name) {
        $db=&JFactory::getDBO();
        $db->setQuery("SELECT * FROM #__feed2post_config WHERE name='".$db->getEscaped($name)."'");
        $result=$db->loadAssoc();
        $val=$result['values'];
        return $val;
    }
    
    static function fetchWithCurl($url,$user="",$pass="",$auth=false) {
        $ch=curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        //curl_setopt($ch, CURLOPT_NOBODY, true);
        if ($auth) {
            curl_setopt($ch , CURLOPT_USERPWD,$user.':'.$pass);
        }
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0) Feed2post");
        $ret=curl_exec($ch);
        $err=curl_errno($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code>=200 && $http_code<300) {
            if ($err!=0) {
                $ret=false;
                echo "Error, Curl failde to fetch:".$err;
            } 
        } else {
            echo "ERRROR, ".$http_code. "Web server says:".$err. " Tried to fetch - $url -<br/>\n";
            if (DEBUG) debug_print_backtrace();
            $ret=false;
        }    
        return $ret;
    }
    
    function insertImageIntoQueue($url) {
        $db=JFactory::getDbo();
        $query="INSERT INTO #__feed2post_queue VALUES ('','$url','image','0')";
        $db->setQuery($query);
        $db->query();
    }
    
    function getUniqueName($url) {
        $name=md5($url);
        $count=0;
        while(file_exists(F2PIMAGEPATH.DS.$name)) {
            $count++;
            $name=$name.$count;
        }   
        return $name;
    }
}
