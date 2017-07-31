<?php
	require_once __DIR__."/../compilers/CoffeeScript/Init.php";

	// Load manually
	CoffeeScript\Init::load();

	echo CoffeeScript\Compiler::compile(
		file_get_contents($_SERVER['PHP_SELF']),
		array('filename' => $_SERVER['PHP_SELF'])
	);
