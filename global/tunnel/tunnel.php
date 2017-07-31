<?php
    Abstract Class Tunnel
    {
        Public Static $url;
        Public Static $ext;
        Public Static $mime_type;
        Public Static $handler;

		Public Static $cache_path;
		Public Static $cache_file;
		Public Static $cache_folder;

        Public Static Function run()
        {
            self::$url = realpath("..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.str_replace("/", DIRECTORY_SEPARATOR, $_GET['tunnel']);
            //self::$url = realpath("..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR).str_replace("/", DIRECTORY_SEPARATOR, $_SERVER['REDIRECT_URL']);

			//trigger_error($_SERVER['REQUEST_URI']);
            self::$ext = pathinfo(self::$url, PATHINFO_EXTENSION);
			// echo self::$url;

            $_SERVER['PHP_SELF'] =
            $_SERVER['SCRIPT_NAME'] =
            $_SERVER['SCRIPT_FILENAME'] = self::$url;

            unset($_GET['tunnel']);
            $_SERVER['QUERY_STRING'] = http_build_query($_GET);

            self::$mime_type = json_decode(__DIR__.DIRECTORY_SEPARATOR."mime_types.json");
			self::$mime_type = self::$mime_type[self::$ext];

            if(strpos(self::$mime_type, "php") == false)
                header('Content-Type: '.self::$mime_type);


            if(file_exists(self::$handler = __DIR__.DIRECTORY_SEPARATOR."handlers".DIRECTORY_SEPARATOR.self::$ext.".php"))
			{
				// before
					if(self::$ext != "php")
					{
						self::$cache_folder = __DIR__.DIRECTORY_SEPARATOR."cache";

						if (!file_exists(self::$cache_folder))
						{
							mkdir(
								self::$cache_folder,
								0777,
								true
							);
						}

						self::$cache_path = (
							self::$cache_folder
							.DIRECTORY_SEPARATOR
							.md5($_SERVER['PHP_SELF'])
						);

						if(
								file_exists(self::$cache_path)
							&&	file_get_contents(self::$cache_path) != ""
							&&	!isset($_GET['nocache'])
							&&	filemtime(self::$cache_path) > filemtime($_SERVER['PHP_SELF'])
						)
						{
							// echo self::$cache_path;
							self::$cache_file = fopen(self::$cache_path, "r");
							echo fread(
								self::$cache_file,
								filesize(self::$cache_path)
							);
							fclose(self::$cache_file);
							die;
						}
						else
						{
							self::$cache_file = fopen(self::$cache_path, "w");
							ob_end_flush();

							ob_start();
						}
					}

				// call
                	require_once self::$handler;

				// after
					if(self::$ext != "php")
					{
						try
						{
							if(!empty($cache_file))
							register_shutdown_function(
								function($cache_File)
								{
									fwrite($cache_file, ob_get_contents());
									fclose($cache_file);
								},
								self::$cache_file
							);
						}
						catch(Exception $ex)
						{

						}
					}
			}
            else
			{
                require_once self::$url;
			}
        }
    }

    Tunnel::run();
