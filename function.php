<?php

function getScreenshot ($url, $args){
	$access_key = ""; #removed intentionally
	$secret_keyword = ""; #removed intentionally

	$params['url'] = urlencode($url);
	$params+= $args;

	foreach ($params as $key => $value){ //create the query string based on the options
		$parts[]= "$key=$value";
	}
	$query = implode ("&", $parts); //compile the query string

	$secret_key = md5($url . $secret_keyword); //generate secret key from url and keyword
	return "https://api.screenshotlayer.com/api/capture?access_key=$access_key&secret_key=$secret_key&$query";
}

if (isset($_GET['query'])){
	# Use the Curl extension to query Google and get back a page of results
	$url = "https://www.google.com/search?q=" . $_GET['query'];
	$ch = curl_init();
	$timeout = 5;
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$html = curl_exec($ch);
	curl_close($ch);

	# Create a DOM parser object
	$dom = new DOMDocument();

	# Parse the HTML from Google.
	# The @ before the method call suppresses any warnings that
	# loadHTML might throw because of invalid HTML in the page.
	@$dom->loadHTML($html);

	# Iterate over all the <a> tags
	$list = array();
	foreach($dom->getElementsByTagName('a') as $link) {
	        # Show the <a href>
	        #echo $link->getAttribute('href');
	        array_push($list,$link->getAttribute('href'));
	        #echo "<br />";
	}
	#echo "<br>";
	#echo (getAttribute('href'));
	
	foreach ($list as $key => $value){
		if (!(strpos($value,'http') !== false)) {
			unset($list[$key]);
		}
		elseif (strncmp(substr($value,0,6), '/url?q=',6) === 0){
			$list[$key] = substr($value,7);
		}
		if (!(strpos($value,$_GET['query']) !== false)) {
			unset($list[$key]);
		}
	}
	$list = array_values(array_filter($list));
	print("<center><h1>Search results for: " . $_GET["query"]. "</h1></center><br>");
	print("<center><h4>Click on a screenshot to visit its respective website.</h4></center>");
	for($x = 0; $x < 5; $x++) {
    	print "<br>";
    	$params['delay'] = '5';
		$formURL = $list[$x];
		$call = getScreenshot($formURL,$params);
		$str = file_get_contents($call);
		$json = json_decode($str, true); // decode the JSON into an associative array;
		echo '<pre>' . print_r($json, true) . '</pre>';
		print("<center><a href= $formURL><img src = ". $call . " width = '700'></a></center><br>");
	}

}


?>