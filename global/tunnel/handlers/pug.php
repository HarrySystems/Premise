<?php
	require_once(
		__DIR__
		.DIRECTORY_SEPARATOR
		.".."
		.DIRECTORY_SEPARATOR
		."compilers"
		.DIRECTORY_SEPARATOR
		."pug"
		.DIRECTORY_SEPARATOR
		."vendor"
		.DIRECTORY_SEPARATOR
		."autoload.php"
	);
	use \Pug as Pug;



	$base = "../../";
	require_once (__DIR__."/../compilers/Premise/Premise.php");
    set_include_path(dirname($_SERVER['PHP_SELF']));
	Premise::run(
		str_replace(
			".pug",
			".php", 
			$_SERVER['PHP_SELF']
		)
	);

	echo (new Pug\Pug())->render($_SERVER['PHP_SELF'], array());

