<?php

/*
	proxy.php - grabs the proxy
*/

//////////////////////////////////////////////////////////
//  	Scrape Proxy IP addresses from hidemyass.com	//
//////////////////////////////////////////////////////////
// hidemyass.com has been 0wned by hysterix

// $header - the browser header
// $agent  - the user agent string to mask as
// returns - an array [0] - ip address [1] - port
function grabproxy($header,$agentstr) {

	// hidemyass.com got hax'd by hysterix!
	// these are the 'ghetto tables'; md5's
	// of an image we compare to when grabing proxies
	// so we can grab the port number with ease.
	// shit loads faster than scanning each pixel 
	// in each image, and easier to implement.
	// pretty hax if you ask me, obviously they 
	// don't want people doing this! The first rule of fight club....
	// Also, if you do the md5 locally to just the file it is different from these.
	// I believe this is the md5 of the header as well as the image together,
	// don't think they are wrong when they don't compare on your system
	$ghetto_tables = array(
		'2741898dc5492442a60c48fd8af5f914' => '80',
		'27760f111201b87996e9d5f1f55e1e2b' => '81',
		'8c4d39d1386fdb4dc6312a00387ae8be' => '444',
		'38925677a3ab9071d81b50d0e4d14ce0' => '1080',
		'5969ad3f3cb42fc2cfc32e667b64db4d' => '1260',
		'37357c1363a6edac318a513c8a16733a' => '2301',
		'380ac73e41c1c9f4b460b6aa74fbaea5' => '3124',
		'70846fe60a6915b3979033fe53f46402' => '3128',
		'9407ba7a72999731d6cefdc41a7522b4' => '33655',
		'53fc62ce0db7912b5be729849ef11dff' => '34387',
		'9229f0e355a3311d1f6b196ffce7cb74' => '6588',
		'c1865c575ac4645f47b603deaf98ca60' => '6654',
		'a580f19646451c4d832303b596fe6248' => '6666',
		'2980cfed592e0e31f9b0f30e24e23bf2' => '8000',
		'9739217797951fd6525313a762007561' => '8080',
		'0b269894b8fc6b74adb92840528b4e33' => '8118',
		'14509909876456e7539e95a9a104fcdc' => '8888',
		'3afb17a502f997278ec096e3347edace' => '9090',
		'7109cafc0782511575f4dc40e932d2e7' => '9188',
		'963b608d579bd4a2110e41b11eb6a12a' => '51898',
		'703a56c126717b1e6791915eee0ed3d8' => '65208'
	);
	
	// Gather an array of proxy lists (About 400 proxies in this list)
	$ass_hiders[0] = "http://hidemyass.com/proxy-list/All-Countries/fast/hide-planetlab/1/";
	$ass_hiders[1] = "http://hidemyass.com/proxy-list/All-Countries/fast/hide-planetlab/2/";
	$ass_hiders[2] = "http://hidemyass.com/proxy-list/All-Countries/fast/hide-planetlab/3/";
	$ass_hiders[3] = "http://hidemyass.com/proxy-list/All-Countries/fast/hide-planetlab/4/";
	$ass_hiders[4] = "http://hidemyass.com/proxy-list/All-Countries/fast/hide-planetlab/5/";
	$ass_hiders[5] = "http://hidemyass.com/proxy-list/All-Countries/fast/hide-planetlab/6/";
	$ass_hiders[6] = "http://hidemyass.com/proxy-list/All-Countries/fast/hide-planetlab/7/";
	$ass_hiders[7] = "http://hidemyass.com/proxy-list/All-Countries/fast/hide-planetlab/8/";
	$ass_hiders[8] = "http://hidemyass.com/proxy-list/All-Countries/fast/hide-planetlab/9/";
	$ass_hiders[9] = "http://hidemyass.com/proxy-list/All-Countries/fast/hide-planetlab/10/";
	$ass_hiders[10] = "http://hidemyass.com/proxy-list/All-Countries/fast/hide-planetlab/11/";
	$ass_hiders[11] = "http://hidemyass.com/proxy-list/All-Countries/fast/hide-planetlab/12/";
	$ass_hiders[12] = "http://hidemyass.com/proxy-list/All-Countries/fast/hide-planetlab/13/";
	$ass_hiders[13] = "http://hidemyass.com/proxy-list/All-Countries/fast/hide-planetlab/14/";
	$ass_hiders[14] = "http://hidemyass.com/proxy-list/All-Countries/fast/hide-planetlab/15/";

	$randkey = array_rand($ass_hiders); // Select a random $ass_hiders page
	$failed = false;

	//$random_hole = rand(1, count($ass_hiders));
	//foreach ($ass_hiders as $ass_hider) {  // UR DOIN IT WRONG
	
	$curl = new CURL();  // create the curl instance
	$opts = setBrowser(0,0,$header,$agentstr);  // do not set first variable to 1; we don't need infinite looping

	$curl->retry = MAX_RETRYS;
	$curl->addSession($ass_hiders[$randkey],$opts[0]);
	
	ob_start();	// this is a hack if i've ever seen one
	$result = $curl->exec();  // this is the site returned
	ob_end_clean(); // without this, php likes to output a 1 to the screen (something to do with the header info probably)
	// unset curl 		
	$curl->clear();  // remove the curl instance
	unset($curl);

	if(is_array($result)) {
		$result = $result[0].$result[1];
	}

	$matches = array();
	$yanoob  = array();

	$html = str_get_html($result);
	$arrInput = array();
	foreach($html->find('table') as $t)  {
		$x=0;
		$matches = array();
		foreach($t->find('tr') as $rows) {
			foreach($rows->find('td') as $columns) {
				$str = $columns->outertext;

				// Strip out IP's and load into array $matches

				/* keep the wall of shame up */
				//$pattern = '|(\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b)|U';
				//$pattern = '/^(1\d{0,2}|2(\d|[0-5]\d)?)\.(1\d{0,2}|2(\d|[0-5]\d)?)
				//	   \.(1\d{0,2}|2(\d|[0-5]\d)?)\.(1\d{0,2}|2(\d|[0-5]\d)?)$/';
				//$pattern = '/^(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:[.](?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}$/';
			

				$pattern = '|(\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b)|U';
				if(preg_match($pattern,$str,$yatmparr)) { 	// we found an ip
					$yanoob[0] = $yatmparr[0]; // prepare the ip for the array
					unset($yatmparr);
				} 

				foreach($columns->find('img') as $yaimg) {
					$tehport = $yaimg->alt;
					if(strcmp($tehport,"port") == 0) { // grab the port url
						$portsrc = $yaimg->src;
						$assUrl  = split_url($ass_hiders[$randkey]);
						$portUrl = split_url($portsrc);
				  
				    		if(isset($portUrl['scheme'])) { // path absolute, use as-is
				      			$theport = $arrPost['url'];
				    		} else { // no host, path relative, slap this on the back of the original url; great success!
				    			$newpath = str_replace_once("/","",$portUrl['path']); // remove the leading slash if it xists
				    			$theport = $assUrl['scheme']."://".$assUrl['host']."/".$newpath.'?'.$portUrl['query'];
				    		}


						$curl = new CURL();  // create the curl instance
						$opts = setBrowser(0,0,$header,$agentstr);  // do not set first variable to 1
						$curl->retry = MAX_RETRYS;
						$curl->addSession($theport,$opts[0]);
	
						ob_start();	// this is a hack if i've ever seen one
						$result = $curl->exec();  // this is the png returned
						ob_end_clean(); // without this, php likes to output a 1 to the screen (something to do with the header info probably)
						// unset curl 		
						$curl->clear();  // remove the curl instance
						unset($curl);

						if(is_array($result)) { 
							$result = $result[0].$result[1];
						}

						//print_r($result);

						// ingenuity wins out again; hysterix -1 ; hidemyass - 0
						// they can slow us down but they cant stop us
						
						$portnum = retPortNum($result,$ghetto_tables);
						if(isset($portnum)) { // everything worked; we only want ip's with ports
							$yanoob[1] = $portnum;
							array_push($matches,$yanoob);
						}
					}
				}
			}
		}
	}

	$html->clear(); // if you dont include these two statements, the damn
	unset($html);	// simple html dom becomes simple memory leaker 2.0 because
	unset($result);	// of a fucking "php5 circular references memory leak" - nigger tits
	unset($agentstr);		

	//Grab a random IP from array $matches
	$randkey = array_rand($matches,1);
	$newprox = $matches[$randkey];
	
	return $newprox;
}

?>
