<?php
    $base = "../../";
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
	
    set_include_path(dirname($_SERVER['PHP_SELF']));
    Premise::run($_SERVER['PHP_SELF']);
	// echo $_SERVER['PHP_SELF'];

	// echo dirname($_SERVER['PHP_SELF']));
	require $_SERVER['PHP_SELF'];
	