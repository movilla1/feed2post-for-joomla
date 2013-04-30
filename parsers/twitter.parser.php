<?php
/**
 * rss source parser for feed2post v3.0
 * @author Mario O. Villarroel
 * @copyright 2010 - Elcan Software
 * @license GPLv1
 * @abstract this parser will process rss entries from twitter's user time line and return the text for f2p to use.
 */
defined('_JEXEC') or die('Restricted access');
require_once (JPATH_COMPONENT_ADMINISTRATOR . DS . "parsers" . DS .
    'F2pParsers.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS."parsers".DS."twitter".DS."twitteroauth.php");

class twitterF2pParser extends F2pParser
{
    var $key="nSVC3CRjd5WJLQuRMuHIjA";
    var $secret="LOckDFul2tzbtoIEiWdY4MleKsn1hRDfvtzKAbPEZv4";
    var $pin;
    var $oauth_token;
    var $oauth_token_secret;
    var $access;
    
    function GetOptions() {
        $str="<h2>Twitter Parser</h2>";
        if (strlen(trim($this->oauth_token))>2 && strlen(trim($this->oauth_token_secret))>2 && strlen(trim($this->pin))>2) {
            $str.= JText::_("RESET_TWITTER_STR");
            $str.="<label for='parseroptionpin'>PIN:</label><input type='text' name='parseroption[twitter-pin]' value='$this->pin'/>";
            
        } else {
            $to=new TwitterOAuth($this->key,$this->secret);
            $tok=$to->getRequestToken("oob");
            $this->oauth_token=$tok['oauth_token'];
            $this->oauth_token_secret=$tok['oauth_token_secret'];
            $link=$to->getAuthorizeURL($tok,"");
            $site=&JURI::getInstance();
            $url=$site->root();
            $str.= JText::_("TWITTER_PLEASE")."<br/><a href='$link' target='_blank'><img src='{$url}components/com_feed2post/twitter_button_5_hi.gif' alt='Twitter Login'/></a><br/>";
            $str.= "<br/>";
            $str.= "<label for='parseroptionpin'>PIN:</label><input type='text' name='parseroption[twitter-pin]' value='$this->pin'/>";
        }
        $str.="<input type='hidden' name='parseroption[twitter-oauth_token]' value='".$this->oauth_token."'/>";
        $str.="<input type='hidden' name='parseroption[twitter-oauth_token_secret]' value='".$this->oauth_token_secret."'/>";
        $str.="<div style='clear: both'></div>";            
        return $str;
    }
    
    function setOptions($json_string) {
        $token=json_decode($json_string,true);
        $this->oauth_token=$token['oauth_token'];
        $this->oauth_token_secret=$token['oauth_token_secret'];
        $this->pin=$token['pin'];
        $this->access=(isset($token['access']))?$token['access']:0;
    }
    
    function getPannelName() {
        return JText::_("Twitter Parser Options");
    }
    
    function getItems($row, $config)
    {
        $indexRetrn=0;
        parent::getItems($row, $config);
        $urllines = split("\n", chop(trim($this->srcUrl)));
        //do twitter auth first...
        //OAUTH HERE.
        if ($this->access==0) {
            $db=&JFactory::getDBO();
            $to = new TwitterOAuth($this->key, $this->secret, $this->oauth_token,$this->oauth_token_secret);
            $actk=$to->getAccessToken(NULL,$this->pin);
            $actk['access']=1;
            $actk['pin']=$this->pin;
            $str=json_encode($actk);
            $dbq="UPDATE #__feed2post_config SET values='".$db->getEscaped($str)."' WHERE name='twitter'";
            $db->setQuery($dbq);
            $db->query();
        } else {
            $actk['oauth_token']=$this->oauth_token;
            $actk['oauth_token_secret']=$this->oauth_token_secret;
        }
        $oauth = new TwitterOAuth($this->key, $this->secret, $actk['oauth_token'], $actk['oauth_token_secret']);
        //then process the users requested.
        $allowabletags=$row->allowabletags;
        foreach ($urllines as $url) {
            $twittusr=html_entity_decode($url);
            //$rss = fetch_rss("http://api.twitter.com/1/statuses/user_timeline.rss?screen_name=$twittusr&trim_user=1");
            //twitter fetch timeline for each usr name in the list...
            $result=$oauth->get("statuses/user_timeline",array("screen_name"=>$twittusr,"trim_user"=>1));
            foreach ($result as $item) {
                $searchFrom=$item->text;
                $item_date=date("Y-m-d H:i:s",strtotime($item->created_at));
                $item_link="http://www.twitter.com/$twittusr/status/".$item->id_str;
                if (preg_match($this->keywords, $searchFrom)) {
                    //matching items
                    if (strlen(trim($this->negKeywords)) > 0 && !preg_match($this->negKeywords, $searchFrom)) {
                        $return_str[$indexRetrn] = rawurlencode(json_encode(array('title' => $twittusr." - ".$item_date,
                            "description" => $item->text, "content" => $item->text."<br/>".$item->source, "date" => $item_date,
                            "link" => $item_link, "guid" => $item->id_str, "author" => $twittusr, "images" =>"")));
                        $indexRetrn++;
                    } else {
                        if (strlen(trim($this->negKeywords)) <= 0) {
                          $return_str[$indexRetrn] = rawurlencode(json_encode(array('title' => $twittusr." - ".$item_date,
                            "description" => $item->text, "content" => $item->text."<br/>".$item->source, "date" => $item_date,
                            "link" => $item_link, "guid" => $item->id_str, "author" => $twittusr, "images" =>"")));
                          $indexRetrn++;
                        }
                    }
                }
            }
        }
        return $return_str;
    }
    
    function getHref($link) {
        $value=preg_match("/<a.*?href.(\"|')(.*?)(\"|').*?>/i",$link,$match);
        return $match[2];
    }
}
