<?php
	$token = "8ec35ce928013fc47f737c1777d94631976d00e3";
    $base_url = "http://api.github.com/";
    
	curl_setopt_array(
	    $curl = curl_init(),
	    [
	        CURLOPT_HTTPHEADER => array(
	            'Authorization: Bearer '.$token,
	            'User-Agent: umbler'
	        ),
	        CURLOPT_RETURNTRANSFER => true,
	        CURLOPT_HEADER => false,
	        CURLOPT_FOLLOWLOCATION => true
	    ]
	);
	
	curl_setopt(
	    $curl,
	    CURLOPT_URL,
	    $base_url."user/repos/"
	);
	
	$data = curl_exec($curl);
	echo "<pre>".printf($data, true)."</pre>";
	curl_close($curl);