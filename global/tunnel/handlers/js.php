<?php
	$preprocess = $_SERVER['PHP_SELF'].".php";
	
	if(file_exists($preprocess))
	{
		require_once (
			__DIR__.
			DIRECTORY_SEPARATOR.
			"..".
			DIRECTORY_SEPARATOR.
			"compilers".
			DIRECTORY_SEPARATOR.
			"Premise".
			DIRECTORY_SEPARATOR.
			"Premise.php"
		);
		
		Premise::partial(
			"self",
			basename($_SERVER['PHP_SELF']).".php"
		);
	}
	else
	{
		echo file_get_contents($_SERVER['PHP_SELF']);
	}