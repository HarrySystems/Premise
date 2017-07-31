<?php
	// echo "teste";
	Abstract Class Premise
	{
		// Public Static $modules = array();
		// Public Static $debug = array();
		Public Static $curl;

		Public Static $protocol;

		Private Static $references = array(
			"global" => "../../../",// relative to premise location
			"project" => "../../../../",
			"local" => null// as it's relative to nothing, it'll consider the requested folder.
		);

		Public Static $modules = array(
			"prepend" => array(),
			"append" => array()
		);

		// $_SERVER['HTTPS']

		Private Static Function read($config_path)
		{
			if(file_exists($config_path))
				return @parse_ini_file($config_path);
		}

		Public Static Function path($reference, $path)
		{
			return str_replace(
				self::$protocol."://".$_SERVER['SERVER_NAME'],
				$_SERVER['DOCUMENT_ROOT'],
				str_replace(
					"/",
					DIRECTORY_SEPARATOR,

					self::link(
						$reference,
						$path
					)
				)
			);
		}

		Public Static Function link($reference, $path)
		{
			return self::$protocol."://".str_replace(
				"\\",
				"/",
				str_replace(
					strtolower(realpath($_SERVER['DOCUMENT_ROOT'])),
					$_SERVER['SERVER_NAME'],
					strtolower(
						(
									array_key_exists(
										$reference,
										self::$references//[$reference]
									)
								&& 	$reference != null
							?	realpath(__DIR__."/".self::$references[$reference])
							:	dirname($_SERVER['PHP_SELF'])
						)."/".$path
					)
				)
			);
		}


		Public Static Function run($path)
		{
			self::$protocol = ($_SERVER['SERVER_PORT']  == 443 ? "https" : "http");

			// Set level paths
				$levels = explode(DIRECTORY_SEPARATOR, $path);
				// print_r($levels);
				for($key = count($levels); $key >= 0; $key--)
					$levels[$key] = implode("/", array_slice($levels, 0, $key - 1))."/";
				$levels = array_slice($levels, 4);
				// print_r($levels);
			// Page specific config
				
				$levels[] =	str_replace(
					".".pathinfo($path, PATHINFO_EXTENSION),
					"",
					$path
				);

			// Ignore parent config
				foreach($levels as $key => $level)
				{
					$temp = self::read($level.".premise");
					if 	(
								isset($temp["ignore_parent"])
							&& 	$temp["ignore_parent"]
						)
						$lowest_ignore_parent = $key;
				}
				$levels = array_slice($levels, $lowest_ignore_parent);


			// Load config from levels
				foreach($levels as $level)
					if(file_exists($level.".premise"))
					{
						self::loadAll(
							$level.".premise",
							"prepend"
						);

						register_shutdown_function(
							function($level)
							{
								self::loadAll(
									$level.".premise",
									"append"
								);
							},
							$level
						);
					}

			// require $path;
		}

		// Easier temporary debugging. SHOULD NOT BE USED FOR ERROR HANDLING
			Public Static Function debug(
				$data,
				$dump = true
			)
			{
				ob_start();

				if($dump)
					var_dump($data);
				else
					print_r($data);

				trigger_error(
					str_replace(
						"=>\n  ",
						" => ",
						ob_get_clean()
					),
					E_USER_NOTICE
				);
			}

		Public Static Function partial(
			$reference,
			$request,// custom request if it's an array
			$return = false,
			$parse_method = null
		)
		{
			// checks if it's a ordinary or custom request
				if(!is_array($request))
				{
					$url = self::link($reference, $request);
					$get = $_GET;
					$post = $_POST;
					$cookie = $_COOKIE;
					$auth = "";
				}
				else
				{
					$request = array_change_key_case(
						$request,
						CASE_UPPER
					);

					$parse_url = parse_url($request['URL']);
					$url = (
							!in_array(
								$parse_url['host'],
								array(
									"",
									null,
									$_SERVER['HTTP_HOST']
								)
							)
						?	$request['URL']
						:	self::link($reference, $request['URL'])
					);// calls

					$get = !empty($request['GET']) ? $request['GET'] : array();
					$post = !empty($request['POST']) ? $request['POST'] : array();
					$cookie = !empty($request['COOKIE']) ? $request['COOKIE'] : array();
					$auth = $request['AUTH']['USER'].":".$request['AUTH']['PASS'];
				}

			// Initialize if it hasn't been
				if(empty(self::$curl))
					self::$curl = curl_init();

			// Finds out which method to use for cookie jar in memory only
				if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
					$cookie_jar = "NULL";
				else
					$cookie_jar = "/dev/null";

			// $cookie_jar = __DIR__.DIRECTORY_SEPARATOR."cookie.txt";

			// trigger_error(print_r(http_build_query($cookie), true));
			// sets headers
				curl_setopt_array(
					self::$curl,
					(
							array(
								// CURLOPT_USERAGENT => "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1",
								CURLOPT_URL	=> $url."?".http_build_query($get),
								CURLOPT_RETURNTRANSFER => true,
								CURLOPT_COOKIE => http_build_query($cookie),

								// CURLINFO_CONTENT_TYPE => "text/html",

								// test for crawler calls
								// CURLOPT_ENCODING => 'gzip',
								CURLOPT_FOLLOWLOCATION => true,
								CURLOPT_SSL_VERIFYHOST => 0,
								CURLOPT_SSL_VERIFYPEER => 0,
								// CURLOPT_MAXREDIRS => 999,
								CURLOPT_AUTOREFERER => true,
								// CURLOPT_USERPWD => $auth,
								// CURLOPT_HEADER => true
							)
						+	(
									!empty($post)
								?	array(
										CURLOPT_POST => true,
										CURLOPT_POSTFIELDS => http_build_query($post),
									)
								:	array()
							)
						+	(
										is_array($request)
									&&	!empty($request['HEADERS'])
								?	$request['HEADERS']
								:	array()
							)
					)
				);


			// stop session usage to prevent file lock
				if(isset($_SESSION))
					@session_write_close();

				$result = curl_exec(self::$curl);

				if(curl_exec(self::$curl) === false)
				{
				   	trigger_error(
						curl_error(self::$curl),
						E_USER_ERROR
					);
				}
				else
				{
					if($return)
					{
						// self::info($parse_method);
						if($parse_method !== null)
							return $parse_method($result);
						else
							return $result;
					}
					else
					{
						echo $result;
					}
				}

				if(isset($_SESSION))
					@session_start();
		}

		Private Static Function loadAll(
			$config_path,
			$method
		)
		{
			foreach(self::$references as $key => $reference)
				self::load(
					$config_path,
					$method,
					$key
				);
		}

		Private Static Function load(
			$config_path,
			$method,
			$reference
		)
		{
			$modules_path = strtolower(
					isset(self::$references[$reference])
				?	realpath(__DIR__."/".self::$references[$reference])
				:	dirname(realpath($config_path)) // only for local modules
			).DIRECTORY_SEPARATOR;
			$config = self::read($config_path);

			if(!empty($config[$reference]))
				foreach(array_map('strtolower', $config[$reference]) as $module)
				{
					// do not load the same module twice
						// if(in_array($module, self::$modules[$method])){
						// 	continue;
						// 	// trigger_error($module." already loaded");
						// }
						// else {
						// 	// trigger_error($module." loaded");
						// }
						//
						self::$modules[$method][] = $module;

					$module_config_path = $modules_path.(self::$modules[] = $module).DIRECTORY_SEPARATOR.".premise";

					// Load other required modules
						self::loadAll(
							$module_config_path,
							$method
						);

					// Reference paths
						$int_path = $modules_path.$module;

						$ext_path = self::$protocol."://".
							str_replace(
								"\\",
								"/",
								str_replace(
									strtolower(realpath($_SERVER['DOCUMENT_ROOT'])),
									$_SERVER['SERVER_NAME'],
									$modules_path
								)

						).$module;

						// echo $modules_path."\n";
						// echo $_SERVER['DOCUMENT_ROOT']."\n";
						// echo $ext_path."\n";
						// die;

					// Load files
						$module_config = self::read($module_config_path);
						// print_r($module_config);
						if(!empty($module_config[$method]))
						foreach((array)$module_config[$method] as $file)
						{
							$file_ext = pathinfo($int_path.DIRECTORY_SEPARATOR.$file, PATHINFO_EXTENSION);
// echo $file_ext;
							switch($file_ext)
							{
								case "php":
									require_once $int_path.DIRECTORY_SEPARATOR.$file;
									break;

								case "coffee":
								case "js":
									echo "<script src='".$ext_path."/".$file."'></script>";
									break;

								// case "less":
								case "sass":
								case "scss":
								case "css":
									echo "<link href='".$ext_path."/".$file."' rel='stylesheet'>";
									break;

								default:
									trigger_error(
										"PREMISE: Unsupported filetype ".$file_ext." on module ".$module
									);
									break;
							}
						}
				}
		}
	}
