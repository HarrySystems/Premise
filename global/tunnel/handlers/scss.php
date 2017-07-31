<?php
	require __DIR__
	.DIRECTORY_SEPARATOR
	.".."
	.DIRECTORY_SEPARATOR
	."compilers"
	.DIRECTORY_SEPARATOR
	."scssphp"
	.DIRECTORY_SEPARATOR
	."scss.inc.php";

	use Leafo\ScssPhp\Compiler;
	$scss = new Compiler();
	$buffer = $scss->compile(file_get_contents($_SERVER['PHP_SELF']));
	// minify
	$buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
    $buffer = str_replace(': ', ':', $buffer);
    $buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);
	$buffer = str_replace(array(" {", " }", ", "), array("{", "}", ","), $buffer);
    echo($buffer);
