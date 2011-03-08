<?php

	/*
		nraep_utils.php - functions for nraep
	*/

	// setBrowser - determines how curl interacts with sites
	// $proxy     - 0, 1, or ip of proxy; if 0 no proxy is used; if 1 grab and use a new proxy, otherwise the variable is the proxy
	// $post      - 0, 1, or post string; if 0 dont post, if 1, use method get, otherwise post with the string
	// $header    - the http header information set in config.php 
	// $agent     - an array of user agent strings set in userAgentString.php
	// returns    - [0] - the curl options [1] - the user agent string used [2] - the ip of proxy used (if at all)
	function setBrowser($proxy,$post,$header,$agent) {
		$arrtmp = array();
		if(is_array($agent)) {
			$userAgent = retAgentString($agent);
		} else {
			$userAgent = $agent;
		}
		$opts   = array(
			CURLOPT_HTTPHEADER	=> $header,
			CURLOPT_USERAGENT	=> $userAgent,
			CURLOPT_SSL_VERIFYHOST	=> false,
			CURLOPT_SSL_VERIFYPEER	=> false,
			CURLOPT_FOLLOWLOCATION	=> true,
			CURLOPT_RETURNTRANSFER	=> true,
			CURLOPT_TIMEOUT		=> 15,				
			CURLOPT_ENCODING	=> "gzip"
		);
		
		// they want to use a proxy
		if(strcmp($proxy,0)) {  // $proxy not equal to 0
			
			if(isset($_SESSION['goodproxy'])) { 	 // if a good cached proxy already exists, use it
				$proxy = $_SESSION['goodproxy'];
			}

			if(strlen($proxy) > 1 ) {
				$prox = $proxy;
			}
			else {
				$proxythingy = grabproxy($header,$userAgent);   // mask us when grabbing the proxy too
				$prox 	     = $proxythingy[0].':'.$proxythingy[1];
			}

			$tmpar = array( CURLOPT_PROXY	=>  $prox,
					CURLOPT_HEADER  => 1);		
			array_push_associative($opts,$tmpar);
			$arrtmp[2] = $prox;  // return the proxy used
		}
		if(strcmp($post,0)) { // $post not equal to 0
			if(strlen($post) > 1 ) {  // use post method
				$tmpar = array( CURLOPT_POST	    =>  true,
						CURLOPT_POSTFIELDS  =>  $post );
			}
			else {	// use get method
				$tmpar = array( CURLOPT_HTTPGET	    =>  true );
			}
			array_push_associative($opts,$tmpar);
		}

		$arrtmp[0] = $opts;	 // the curl options
		$arrtmp[1] = $userAgent; // the user agent used
		return $arrtmp;
	}

	/*
		today it's newsletters, who knows what we may need tomorrow, we need to make sure this isn't 
		hard-coded for newsletters only.  It should be some-what flexible as we are simply looking for forms, entering
		sham data and submitting; not necessarily hard, but we should be able to tell it what kind of forms we are looking
		for and maybe be able to use it on other things as well.
	*/

	// retPost   		 - accepts a multi-dimensional array of multiple forms, and input elements inside them
	// $url[0][0]		 - The urls of where to post to
	// $url[0][1]		 - the method to use 
	// $input[0][0][name]    - name
	// $input[0][0][type]	 - type - button checkbox file hidden image password radio reset submit text
	// $input[0][0][value] 	 - value - whatever is pre-entered into the box upon page load; can help to see what field it is
	// $input[0][0][checked] - checked - whether or not the checkbox or radio button is preselected (the default choice per site)
	// $select[0][0][name]   - name
	// $select[0][0][type]	 - type - option
	// $select[0][0][value]  - value - whatever is pre-entered into the box upon page load; can help to see what field it is
	
	// $output[]	 	 - an array of the data to be posted to each site:
	//		 	 - key names: name - email
	//			 - value:     values for each key name, set through gui form
	// $formType		 - newsletter - determines what type of form to submit to when found
	// 			 - currently, newsletter is the only inmplemented form type as of yet

	// returns		 - returns either an array with post, url, method, or the number 1 to indicate the form in question was not found
	function retPost($url,$input,$select,$output,$formType) {
		switch($formType) {
		case "newsletter":
			$arrToPost = array();
			$found     = false;  // flag
			$x = 0; // counters
			$y = 0; 

			/*
				Traverse our sudo "dom" looking through multiple forms, elements and values.
				Throw the values with the key being the name and the value being the final post value into an array 
				where we add an '&' to the end of each key-value pair and submit that as our form data
			*/
			foreach($input as $key0 => $forms) { 		   	   // traverse multiple forms
				foreach($forms as $key2 => $inputs) {  	  	   // traverse elements in form
					if(strpos($inputs['name'],"]")) { // array, remove key
						$firstopenbracketpos = strripos($inputs['name'],"[");
						$lastendbracketpos   = strripos($inputs['name'],"]");
						$thename = substr($inputs['name'],$firstopenbracketpos,$lastendbracketpos);
						$thename = trim($thename,"[]");
					} else {
						$thename = $inputs['name']; // name = key, value = value
					}
		
					if(strcasecmp($inputs['type'],'hidden') == 0 ) { // if hidden fill with value
						$arrToPost[$y][$thename] = $inputs['value']; // name = key, value = value
					}

					if(strcasecmp($inputs['type'],'text') == 0 ) { // deal with textbox
						if(stristr($thename,'email')) { // search for the word email // name = key, value = value
							$arrToPost[$y][$thename] = urlencode(utf8_decode($output['email'])); 
							$found = true;  // a textbox with name 'email' was found
						} elseif(stristr($thename,'mail')) { // search for the word mail
							$arrToPost[$y][$thename] = urlencode(utf8_decode($output['email'])); 
							$found = true;  // a textbox with name 'newsletter' was found
						} elseif(stristr($thename,'newsletter')) { // search for the word newsletter
							$arrToPost[$y][$thename] = urlencode(utf8_decode($output['email'])); 
							$found = true;  // a textbox with name 'newsletter' was found
						} elseif(stristr($thename,'news')) { // search for the word news
							$arrToPost[$y][$thename] = urlencode(utf8_decode($output['email'])); 
							$found = true;  // a textbox with name 'newsletter' was found
						}
						if(stristr($thename,'name')) { // search for the word name
							$name = $output['name'];
							$arrToPost[$y][$thename] = urlencode(utf8_decode($name)); 
						}

             					 // was going to do more with $url than just pass it through
						$urlToPost = $url[$x][0];
						$urlMethod = $url[$x][1];
					}
						if(strcasecmp($inputs['type'],'radio') == 0) { // deal with radio buttons 			
						  if(isset($inputs['checked']))  // go with the default choice
							 $arrToPost[$y][$thename] = $inputs['value']; // name = key, value = value		
					}
						if(strcasecmp($inputs['type'],'checkbox') == 0) { // deal with checkboxes
							$arrToPost[$y][$thename] = $inputs['value']; // name = key, value = value
					}

				$y++;
				}
			$x++;
			}

			foreach($select as $key0 => $forms) { 		   	   // traverse multiple forms
				foreach($forms as $key2 => $selects) { 	  	   // traverse elements 
					$yatmparrlol = array();
					$yatmparr    = array();
					$yatmparr[$selects['name']] = $selects['value'];
					array_push_associative($yatmparrlol,$yatmparr);  // array stupidity
					array_push($arrToPost,$yatmparrlol);		 // array insanity
				}
			}

			// create the actual post string
			foreach ( $arrToPost as $key => $values) {  // each value to send 
				foreach($values as $key2 => $value) {
					$postItems[] = $key2 . '=' . $value;
				}
				$postString = implode ('&', $postItems);		
			}

			if($found) { // textbox with something we wanted was found
				if((isset($postString)) && (isset($urlToPost)) && (isset($urlMethod))) {
					$arrPost['post']   = $postString;
					$arrPost['url']    = $urlToPost;
					$arrPost['method'] = $urlMethod;
					
					return $arrPost;
				}
				else {
					return 1; // failure
				}

				//print_r($arrPost);exit;
				
				break;
			}
			else {
				return 1; // failure
			}
			
		//case "porn":
			//break;		
		}
	}

	// $retPortNum   - accepts a url path to a hidemyass portnumber image and checks to see if we have that on file (ghetto rainbow tables!)
	// $portimage	 - a png file of the port to be hashed
	// $ghettoTables - an associative array; key => value; md5 hash of picture => portnumber
	// returns       - either returns the port number if found or a 0 if failure
	function retPortNum($portimage,$ghettoTables)
	{
		$newporthash = hash("md5",$portimage);  // dont need sha256, no collisions with so little files
		return $ghettoTables[$newporthash];	// more trickery
	}


	// return a random user agent string as defined in the userAgentString.php file
	// may perhaps extend this some day
	function retAgentString($agent) {
		$randKey = array_rand($agent,1);
		$agentString = $agent[$randKey];
		return $agentString;
	}

	// return a formatted string of the ip address / proxy (browser) for logging
	function retIpToLog() {
		if ($_SERVER['HTTP_X_FORWARDED_FOR']) {
			if ($_SERVER['HTTP_CLIENT_IP']) {
		    		$proxy = $_SERVER['HTTP_CLIENT_IP'];
		  } else {
		  	$proxy = $_SERVER['REMOTE_ADDR'];
		  }

		  $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			if ($_SERVER['HTTP_CLIENT_IP']) {
		    		$ip = $_SERVER['HTTP_CLIENT_IP'];
		  	} else {
		    		$ip = $_SERVER['REMOTE_ADDR'];
		  	}
		}

		if(isset($proxy)) {
			if(strcmp($ip,$proxy) == 0) {
				$ipToLog = $proxy;
			} else {
				$ipToLog = $ip . " through browser proxy ". $proxy;
			}
		} else {
			$ipToLog = $ip;
		}
		
		return $ipToLog;
	}

	// http post without curl
	function do_post_request($url, $data, $optional_headers = null)
	{
	   $params = array('http' => array(
		           'method' => 'POST',
		           'content' => $data
		           ));
	   if ($optional_headers !== null) {
		$params['http']['header'] = $optional_headers;
	   }
	   $ctx = stream_context_create($params);
	   $fp = @fopen($url, 'rb', false, $ctx);
	   if (!$fp) {
		//throw new Exception("Problem with $url, $php_errormsg");
	   }
	   $response = @stream_get_contents($fp);
	   if ($response === false) {
		//throw new Exception("Problem reading data from $url, $php_errormsg");
	   }
	
	   return $response;
	  }

	// Append associative array elements because php does not support this natively :/
	function array_push_associative(&$arr) {
	   $args = func_get_args();
	   foreach ($args as $arg) {
	       if (is_array($arg)) {
		   foreach ($arg as $key => $value) {
		       $arr[$key] = $value;
		       $ret++;
		   }
	       }else{
		   $arr[$arg] = "";
	       }
	   }
	   return $ret;
	}

	// find the least common multiple of two numbers
	function lcm($a, $b) {
		return ( $a / gcm($a,$b) ) * $b;
	}

	// str_replace only once; how is this not supported natively :/
	function str_replace_once($search, $replace, $subject) {
	    $firstChar = strpos($subject, $search);
	    if($firstChar !== false) {
		$beforeStr = substr($subject,0,$firstChar);
		$afterStr = substr($subject, $firstChar + strlen($search));
		return $beforeStr.$replace.$afterStr;
	    } else {
		return $subject;
	    }
	}

?>
