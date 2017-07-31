<!DOCTYPE html>
<html>
	<head>
		<?php
			require_once __DIR__."/../compilers/Parsedown/parsedown.php";
			$Parsedown = new Parsedown();

			$style = str_replace(
				$_SERVER['DOCUMENT_ROOT'],
				"http://".$_SERVER['HTTP_HOST'],
				str_replace("\\", "/",realpath(__DIR__."/../compilers/Parsedown/github.css"))
			);
		?>
		<link href='<?= $style ?>' rel='stylesheet'/>
	</head>

	<body class='markdown-body'>
		<?= $Parsedown->text(file_get_contents($_SERVER['PHP_SELF'])) ?>
	</body>
</html>
