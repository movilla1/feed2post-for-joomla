<?php
    /**
     * Base storage engine class, shows the interface and basic i/o functions.
     * @author Mario O. Villarroel
     * @copyright 2010 - Elcan Software
     * @license GPLv1
     * @abstract This base class has the interface elements for f2p to use and the base i/o mechs.
     */
  class F2pEngine {
     /**
      * Function that must be extended by the engine to implement the storage mech.
      * @param rowfeed: feed2post table row
      * @param config: feed2post_config object
      * @param item: rawurlencoded json string
      */
     function store($rowfeed,$config,$item) {
        return true;
     }
     
     /**
      * This function will show the values that can be set, it should
      * return a form elements string, that will be show on the settings
      * page for the plugin.
      * All settings will be stored on the feed2post table, using a json string.
      */  
     function showEngineSettings() {
       return "<br/>"; 
     }
 
     /**
      * This function must be implemented in the plugin, it allows the code to check
      * if the content is already stored, parameters are:
      * @param guid: value to check
      * @param sec_avoid: duplicate avoidance method, (eg. section or section/category)
      * @param opts: options stored for the engine in the storeoptions field on f2p table
      */
     function checkGuid($guid, $sec_avoid = false, $opts) {
        return false;
     }
  }