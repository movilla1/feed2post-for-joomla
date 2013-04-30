<?php
defined ("_JEXEC") or die("No entry point, please use the proper init");

/**
 * rss source parser for feed2post v3.0
 * @author Mario O. Villarroel
 * @copyright 2010 - Elcan Software
 * @license GPLv1
 * @abstract this parser will process NewsML entries and return the title, text and images for f2p to use
 */
require_once (JPATH_COMPONENT_ADMINISTRATOR . DS . "parsers". DS .'F2pParsers.php');

class afpnewsmlF2pParser extends F2pParser {
    var $basepath;
    function getOptions() {
        $ret="<h2>AFP News Agency</h2>";
        $ret.=JText::_("ASSET_BASEPATH_STR")."<br/><br/>";
        $ret.="<label for='base'>".JText::_("ASSET_BASEPATH")."</label><input type='text' name='parseroption[afp_basepath]' value='".$this->basepath."'/><br/>";
        return $ret;
    }
    
    function setOptions($json_string) {
        $opt=json_decode($json_string,true);
        if (isset($opt['afp_basepath'])) $this->basepath=$opt['afp_basepath']; else $this->basepath=JPATH_ROOT.DS."media";
        return true;
    }
    
    function getPannelName() {
        return JText::_("AFP NewsML Parser Options");
    }
    
    function getItems($row, $config) {
        $datacontent_query = "NewsComponent/ContentItem[MediaType/@FormalName='Text']/DataContent";
        $format_quicklook_query = "NewsComponent[@Duid='%s']/NewsComponent[Role/@FormalName='Quicklook']";
        $format_thumbnail_query = "NewsComponent[@Duid='%s']/NewsComponent[Role/@FormalName='Thumbnail']";
        $urllines = split("\n", chop(trim($this->srcUrl)));
        $allowabletags=$row->allowabletags;
        foreach ($urllines as $url) {
            $fetched=$this->fetchWithCurl($url);
            $urlparts=parse_url($url);
            $baseurl=$urlparts['proto']."://".$urlparts['host']."/".$urlparts['path'];
            if (strlen($fetched)>3) {
              $xml=simplexml_load_string($fetched);
              $item_guid=$xml->NewsItem->Identification->NewsIdentifier->PublicIdentifier;
              $newsdate=$xml->NewsItem->NewsManagement->ThisRevisionCreated;
              $isodate=substr($newsdate,0,4)."-".substr($newsdate,5,2)."-".substr($newsdate,7,2)." ".
                       substr($newsdate,10,2).":".substr($newsdate,12,2).":".substr($newsdate,14,2);
              foreach ($xml->NewsItem->NewsComponent->NewsComponent as $newscomponent) {
                $item_file = sprintf('%s/%s', $baseurl, $newscomponent->NewsItemRef['NewsItem']);
                $item_link = substr($newscomponent->NewsItemRef['NewsItem'], 0, -4);
                $item_title=$newscomponent->NewsLines->HeadLine;
                
                $xmlitem = simplexml_load_file($item_file);
                $datacontent = current($xmlitem->NewsItem->NewsComponent->xpath($datacontent_query));
                if ($datacontent->media) {
                    $image_reference = $datacontent->media->{'media-reference'}['datalocation'];
                    $xpath_query = sprintf($format_thumbnail_query, substr($image_reference, 1));
                    $image_node = current($xmlitem->NewsItem->NewsComponent->xpath($xpath_query));
                    $image_name = $image_node->ContentItem['Href'];
                    $item_image=sprintf('<img src="%s/%s" alt="" style="float:%s;margin:5px;"></a>'."\n", $this->basepath, 
                                        $image_name, ($datacontent->media['style'] == 'leftSide') ? 'left':'right');
                }
                $item_full=$datacontent->p[1];
              }
            } else 
              return "Can't Open the source<br/>Error:".$fetched."<br/>\n";
            //we have xml parsed into the $parsed var.
        }
        return false;
    }

}