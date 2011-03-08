<?php

/*
	config.php - config for nraep; lets try to be somewhat organized...
*/

include_once('curl.php');    		// very nice curl wrapper 
include_once('list.php');    		// default newsletter list (from partyvan)
include_once('proxy.php');   		// returns a proxy from hidemyass
include_once('nraep_utils.php');	// functions for nraep
include_once('split_url.php');  	// great library for dealing with urls
include_once('simple_html_dom.php');    // stop re-inventing the wheel
include_once('userAgentString.php');	// list of user agent strings

set_time_limit(0); 		// stop timeouts (in our script, not necessarily in a requested site)
ini_set('memory_limit','128M'); // if you want to raep 10 or higher at once, you need memory

/* metadata for nraep */
define('VERSION','0.5');
define('TITLE','for all your newsletter needs');

/* funcionality for nraep */
define('MAX_CONNECTIONS',10);     // default max number of simultaneous curl connections
define('MAX_RETRYS',2);	  	  // max number of retrys per site
define('FORM_TYPE',"newsletter"); // type of form to fetch and submit to - only newsletters of yet

/* logging for nraep */
define('ENABLE_LOG',true);	      // default true  - enable logging
define('ENABLE_STEALTH_LOG',false);   // default false - log ip's of users   true - do not log ip's of users
define('DEBUG_LEVEL',0);	      // debug level   - 0 - typical usage logs (default)  1 - verbose - more information than the typical user wants/needs

/* paths for nraep */
define('LOG_PATH','logs/');	      // the path to save the logs inside the nraep directory; leave the trailing slash if you want it to work

/* header info for curl (browser info needed its own function) */
$header	= array( 
	"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.8",
	"Accept-Language: en-us,en;q=0.5",
	"Cache-Control: max-age=0",
	"Accept-Encoding: gzip,deflate",
	"Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7",
	"Keep-Alive: 300",
	"Connection: keep-alive",
	"Pragma: " // browsers keep this blank. 
);

// TIDY SAVAGED OUR WOMENS
/* tidy configuration 
$tidy_config    =    array(
	'show-body-only'             =>    true,
	'wrap'                       =>    '0'
); 
*/
/*  // old way
	'clean'                      =>    true,
	'drop-font-tags'             =>    true,
	'drop-proprietary-attributes'=>    true,
	'drop-empty-paras'	     =>    true,
	'hide-comments'		     =>    true,
	'join-classes'		     =>    true,
	'join-styles'		     =>    true,
	'show-body-only'             =>    true,
	'word-2000'                  =>    true,
	'wrap'                       =>    '0'
*/

?>
