<?php  namespace think;
class Request 
{
	protected static $instance = NULL;
	protected $method = NULL;
	protected $domain = NULL;
	protected $url = NULL;
	protected $baseUrl = NULL;
	protected $baseFile = NULL;
	protected $root = NULL;
	protected $pathinfo = NULL;
	protected $path = NULL;
	protected $routeInfo = array( );
	protected $env = NULL;
	protected $dispatch = array( );
	protected $module = NULL;
	protected $controller = NULL;
	protected $action = NULL;
	protected $langset = NULL;
	protected $param = array( );
	protected $get = array( );
	protected $post = array( );
	protected $request = array( );
	protected $route = array( );
	protected $put = NULL;
	protected $session = array( );
	protected $file = array( );
	protected $cookie = array( );
	protected $server = array( );
	protected $header = array( );
	protected $mimeType = array( "xml" => "application/xml,text/xml,application/x-xml", "json" => "application/json,text/x-json,application/jsonrequest,text/json", "js" => "text/javascript,application/javascript,application/x-javascript", "css" => "text/css", "rss" => "application/rss+xml", "yaml" => "application/x-yaml,text/yaml", "atom" => "application/atom+xml", "pdf" => "application/pdf", "text" => "text/plain", "image" => "image/png,image/jpg,image/jpeg,image/pjpeg,image/gif,image/webp,image/*", "csv" => "text/csv", "html" => "text/html,application/xhtml+xml,*/*" );
	protected $content = NULL;
	protected $filter = NULL;
	protected static $hook = array( );
	protected $bind = array( );
	protected $input = NULL;
	protected $cache = NULL;
	protected $isCheckCache = NULL;
	protected $mergeParam = false;
	protected function __construct($options = array( )) 
	{
		foreach( $options as $name => $item ) 
		{
			if( property_exists($this, $name) ) 
			{
				$this->$name = $item;
			}
		}
		if( is_null($this->filter) ) 
		{
			$this->filter = Config::get("default_filter");
		}
		$this->input = file_get_contents("php://input");
	}
	public function __call($method, $args) 
	{
		if( array_key_exists($method, self::$hook) ) 
		{
			array_unshift($args, $this);
			return call_user_func_array(self::$hook[$method], $args);
		}
		throw new Exception("method not exists:" . "think\\Request" . "->" . $method);
	}
	public static function hook($method, $callback = NULL) 
	{
		if( is_array($method) ) 
		{
			self::$hook = array_merge(self::$hook, $method);
		}
		else 
		{
			self::$hook[$method] = $callback;
		}
	}
	public static function instance($options = array( )) 
	{
		if( is_null(self::$instance) ) 
		{
			self::$instance = new static($options);
		}
		return self::$instance;
	}
	public static function destroy() 
	{
		if( !is_null(self::$instance) ) 
		{
			self::$instance = null;
		}
	}
	public static function create($uri, $method = "GET", $params = array( ), $cookie = array( ), $files = array( ), $server = array( ), $content = NULL) 
	{
		$server["PATH_INFO"] = "";
		$server["REQUEST_METHOD"] = strtoupper($method);
		$info = parse_url($uri);
		if( isset($info["host"]) ) 
		{
			$server["SERVER_NAME"] = $info["host"];
			$server["HTTP_HOST"] = $info["host"];
		}
		if( isset($info["scheme"]) ) 
		{
			if( "https" === $info["scheme"] ) 
			{
				$server["HTTPS"] = "on";
				$server["SERVER_PORT"] = 443;
			}
			else 
			{
				unset($server["HTTPS"]);
				$server["SERVER_PORT"] = 80;
			}
		}
		if( isset($info["port"]) ) 
		{
			$server["SERVER_PORT"] = $info["port"];
			$server["HTTP_HOST"] = $server["HTTP_HOST"] . ":" . $info["port"];
		}
		if( isset($info["user"]) ) 
		{
			$server["PHP_AUTH_USER"] = $info["user"];
		}
		if( isset($info["pass"]) ) 
		{
			$server["PHP_AUTH_PW"] = $info["pass"];
		}
		if( !isset($info["path"]) ) 
		{
			$info["path"] = "/";
		}
		$options = array( );
		$options[strtolower($method)] = $params;
		$queryString = "";
		if( isset($info["query"]) ) 
		{
			parse_str(html_entity_decode($info["query"]), $query);
			if( !empty($params) ) 
			{
				$params = array_replace($query, $params);
				$queryString = http_build_query($params, "", "&");
			}
			else 
			{
				$params = $query;
				$queryString = $info["query"];
			}
		}
		else 
		{
			if( !empty($params) ) 
			{
				$queryString = http_build_query($params, "", "&");
			}
		}
		if( $queryString ) 
		{
			parse_str($queryString, $get);
			$options["get"] = (isset($options["get"]) ? array_merge($get, $options["get"]) : $get);
		}
		$server["REQUEST_URI"] = $info["path"] . (("" !== $queryString ? "?" . $queryString : ""));
		$server["QUERY_STRING"] = $queryString;
		$options["cookie"] = $cookie;
		$options["param"] = $params;
		$options["file"] = $files;
		$options["server"] = $server;
		$options["url"] = $server["REQUEST_URI"];
		$options["baseUrl"] = $info["path"];
		$options["pathinfo"] = ("/" == $info["path"] ? "/" : ltrim($info["path"], "/"));
		$options["method"] = $server["REQUEST_METHOD"];
		$options["domain"] = (isset($info["scheme"]) ? $info["scheme"] . "://" . $server["HTTP_HOST"] : "");
		$options["content"] = $content;
		self::$instance = new self($options);
		return self::$instance;
	}
	public function domain($domain = NULL) 
	{
		if( !is_null($domain) ) 
		{
			$this->domain = $domain;
			return $this;
		}
		if( !$this->domain ) 
		{
			$this->domain = $this->scheme() . "://" . $this->host();
		}
		return $this->domain;
	}
	public function url($url = NULL) 
	{
		if( !is_null($url) && true !== $url ) 
		{
			$this->url = $url;
			return $this;
		}
		if( !$this->url ) 
		{
			if( IS_CLI ) 
			{
				$this->url = (isset($_SERVER["argv"][1]) ? $_SERVER["argv"][1] : "");
			}
			else 
			{
				if( isset($_SERVER["HTTP_X_REWRITE_URL"]) ) 
				{
					$this->url = $_SERVER["HTTP_X_REWRITE_URL"];
				}
				else 
				{
					if( isset($_SERVER["REQUEST_URI"]) ) 
					{
						$this->url = $_SERVER["REQUEST_URI"];
					}
					else 
					{
						if( isset($_SERVER["ORIG_PATH_INFO"]) ) 
						{
							$this->url = $_SERVER["ORIG_PATH_INFO"] . ((!empty($_SERVER["QUERY_STRING"]) ? "?" . $_SERVER["QUERY_STRING"] : ""));
						}
						else 
						{
							$this->url = "";
						}
					}
				}
			}
		}
		return (true === $url ? $this->domain() . $this->url : $this->url);
	}
	public function baseUrl($url = NULL) 
	{
		if( !is_null($url) && true !== $url ) 
		{
			$this->baseUrl = $url;
			return $this;
		}
		if( !$this->baseUrl ) 
		{
			$str = $this->url();
			$this->baseUrl = (strpos($str, "?") ? strstr($str, "?", true) : $str);
		}
		return (true === $url ? $this->domain() . $this->baseUrl : $this->baseUrl);
	}
	public function baseFile($file = NULL) 
	{
		if( !is_null($file) && true !== $file ) 
		{
			$this->baseFile = $file;
			return $this;
		}
		if( !$this->baseFile ) 
		{
			$url = "";
			if( !IS_CLI ) 
			{
				$script_name = basename($_SERVER["SCRIPT_FILENAME"]);
				if( basename($_SERVER["SCRIPT_NAME"]) === $script_name ) 
				{
					$url = $_SERVER["SCRIPT_NAME"];
				}
				else 
				{
					if( basename($_SERVER["PHP_SELF"]) === $script_name ) 
					{
						$url = $_SERVER["PHP_SELF"];
					}
					else 
					{
						if( isset($_SERVER["ORIG_SCRIPT_NAME"]) && basename($_SERVER["ORIG_SCRIPT_NAME"]) === $script_name ) 
						{
							$url = $_SERVER["ORIG_SCRIPT_NAME"];
						}
						else 
						{
							if( ($pos = strpos($_SERVER["PHP_SELF"], "/" . $script_name)) !== false ) 
							{
								$url = substr($_SERVER["SCRIPT_NAME"], 0, $pos) . "/" . $script_name;
							}
							else 
							{
								if( isset($_SERVER["DOCUMENT_ROOT"]) && strpos($_SERVER["SCRIPT_FILENAME"], $_SERVER["DOCUMENT_ROOT"]) === 0 ) 
								{
									$url = str_replace("\\", "/", str_replace($_SERVER["DOCUMENT_ROOT"], "", $_SERVER["SCRIPT_FILENAME"]));
								}
							}
						}
					}
				}
			}
			$this->baseFile = $url;
		}
		return (true === $file ? $this->domain() . $this->baseFile : $this->baseFile);
	}
	public function root($url = NULL) 
	{
		if( !is_null($url) && true !== $url ) 
		{
			$this->root = $url;
			return $this;
		}
		if( !$this->root ) 
		{
			$file = $this->baseFile();
			if( $file && 0 !== strpos($this->url(), $file) ) 
			{
				$file = str_replace("\\", "/", dirname($file));
			}
			$this->root = rtrim($file, "/");
		}
		return (true === $url ? $this->domain() . $this->root : $this->root);
	}
	public function pathinfo() 
	{
		if( is_null($this->pathinfo) ) 
		{
			if( isset($_GET[Config::get("var_pathinfo")]) ) 
			{
				$_SERVER["PATH_INFO"] = $_GET[Config::get("var_pathinfo")];
				unset($_GET[Config::get("var_pathinfo")]);
			}
			else 
			{
				if( IS_CLI ) 
				{
					$_SERVER["PATH_INFO"] = (isset($_SERVER["argv"][1]) ? $_SERVER["argv"][1] : "");
				}
			}
			if( !isset($_SERVER["PATH_INFO"]) ) 
			{
				foreach( Config::get("pathinfo_fetch") as $type ) 
				{
					if( !empty($_SERVER[$type]) ) 
					{
						$_SERVER["PATH_INFO"] = (0 === strpos($_SERVER[$type], $_SERVER["SCRIPT_NAME"]) ? substr($_SERVER[$type], strlen($_SERVER["SCRIPT_NAME"])) : $_SERVER[$type]);
						break;
					}
				}
			}
			$this->pathinfo = (empty($_SERVER["PATH_INFO"]) ? "/" : ltrim($_SERVER["PATH_INFO"], "/"));
		}
		return $this->pathinfo;
	}
	public function path() 
	{
		if( is_null($this->path) ) 
		{
			$suffix = Config::get("url_html_suffix");
			$pathinfo = $this->pathinfo();
			if( false === $suffix ) 
			{
				$this->path = $pathinfo;
			}
			else 
			{
				if( $suffix ) 
				{
					$this->path = preg_replace("/\\.(" . ltrim($suffix, ".") . ")\$/i", "", $pathinfo);
				}
				else 
				{
					$this->path = preg_replace("/\\." . $this->ext() . "\$/i", "", $pathinfo);
				}
			}
		}
		return $this->path;
	}
	public function ext() 
	{
		return pathinfo($this->pathinfo(), PATHINFO_EXTENSION);
	}
	public function time($float = false) 
	{
		return ($float ? $_SERVER["REQUEST_TIME_FLOAT"] : $_SERVER["REQUEST_TIME"]);
	}
	public function type() 
	{
		$accept = $this->server("HTTP_ACCEPT");
		if( empty($accept) ) 
		{
			return false;
		}
		foreach( $this->mimeType as $key => $val ) 
		{
			$array = explode(",", $val);
			foreach( $array as $k => $v ) 
			{
				if( stristr($accept, $v) ) 
				{
					return $key;
				}
			}
		}
		return false;
	}
	public function mimeType($type, $val = "") 
	{
		if( is_array($type) ) 
		{
			$this->mimeType = array_merge($this->mimeType, $type);
		}
		else 
		{
			$this->mimeType[$type] = $val;
		}
	}
	public function method($method = false) 
	{
		if( true === $method ) 
		{
			return ($this->server("REQUEST_METHOD") ?: "GET");
		}
		if( !$this->method ) 
		{
			if( isset($_POST[Config::get("var_method")]) ) 
			{
				$this->method = strtoupper($_POST[Config::get("var_method")]);
				$this->$this->method($_POST);
			}
			else 
			{
				if( isset($_SERVER["HTTP_X_HTTP_METHOD_OVERRIDE"]) ) 
				{
					$this->method = strtoupper($_SERVER["HTTP_X_HTTP_METHOD_OVERRIDE"]);
				}
				else 
				{
					$this->method = ($this->server("REQUEST_METHOD") ?: "GET");
				}
			}
		}
		return $this->method;
	}
	public function isGet() 
	{
		return $this->method() == "GET";
	}
	public function isPost() 
	{
		return $this->method() == "POST";
	}
	public function isPut() 
	{
		return $this->method() == "PUT";
	}
	public function isDelete() 
	{
		return $this->method() == "DELETE";
	}
	public function isHead() 
	{
		return $this->method() == "HEAD";
	}
	public function isPatch() 
	{
		return $this->method() == "PATCH";
	}
	public function isOptions() 
	{
		return $this->method() == "OPTIONS";
	}
	public function isCli() 
	{
		return PHP_SAPI == "cli";
	}
	public function isCgi() 
	{
		return strpos(PHP_SAPI, "cgi") === 0;
	}
	public function param($name = "", $default = NULL, $filter = "") 
	{
		if( empty($this->mergeParam) ) 
		{
			$method = $this->method(true);
			switch( $method ) 
			{
				case "POST": $vars = $this->post(false);
				break;
				case "PUT": case "DELETE": case "PATCH": $vars = $this->put(false);
				break;
				default: $vars = array( );
			}
			$this->param = array_merge($this->param, $this->get(false), $vars, $this->route(false));
			$this->mergeParam = true;
		}
		if( true === $name ) 
		{
			$file = $this->file();
			$data = (is_array($file) ? array_merge($this->param, $file) : $this->param);
			return $this->input($data, "", $default, $filter);
		}
		return $this->input($this->param, $name, $default, $filter);
	}
	public function route($name = "", $default = NULL, $filter = "") 
	{
		if( is_array($name) ) 
		{
			$this->param = array( );
			return $this->route = array_merge($this->route, $name);
		}
		return $this->input($this->route, $name, $default, $filter);
	}
	public function get($name = "", $default = NULL, $filter = "") 
	{
		if( empty($this->get) ) 
		{
			$this->get = $_GET;
		}
		if( is_array($name) ) 
		{
			$this->param = array( );
			return $this->get = array_merge($this->get, $name);
		}
		return $this->input($this->get, $name, $default, $filter);
	}
	public function post($name = "", $default = NULL, $filter = "") 
	{
		if( empty($this->post) ) 
		{
			$content = $this->input;
			if( empty($_POST) && false !== strpos($this->contentType(), "application/json") ) 
			{
				$this->post = (array) json_decode($content, true);
			}
			else 
			{
				$this->post = $_POST;
			}
		}
		if( is_array($name) ) 
		{
			$this->param = array( );
			return $this->post = array_merge($this->post, $name);
		}
		return $this->input($this->post, $name, $default, $filter);
	}
	public function put($name = "", $default = NULL, $filter = "") 
	{
		if( is_null($this->put) ) 
		{
			$content = $this->input;
			if( false !== strpos($this->contentType(), "application/json") ) 
			{
				$this->put = (array) json_decode($content, true);
			}
			else 
			{
				parse_str($content, $this->put);
			}
		}
		if( is_array($name) ) 
		{
			$this->param = array( );
			return $this->put = (is_null($this->put) ? $name : array_merge($this->put, $name));
		}
		return $this->input($this->put, $name, $default, $filter);
	}
	public function delete($name = "", $default = NULL, $filter = "") 
	{
		return $this->put($name, $default, $filter);
	}
	public function patch($name = "", $default = NULL, $filter = "") 
	{
		return $this->put($name, $default, $filter);
	}
	public function request($name = "", $default = NULL, $filter = "") 
	{
		if( empty($this->request) ) 
		{
			$this->request = $_REQUEST;
		}
		if( is_array($name) ) 
		{
			$this->param = array( );
			return $this->request = array_merge($this->request, $name);
		}
		return $this->input($this->request, $name, $default, $filter);
	}
	public function session($name = "", $default = NULL, $filter = "") 
	{
		if( empty($this->session) ) 
		{
			$this->session = Session::get();
		}
		if( is_array($name) ) 
		{
			return $this->session = array_merge($this->session, $name);
		}
		return $this->input($this->session, $name, $default, $filter);
	}
	public function cookie($name = "", $default = NULL, $filter = "") 
	{
		if( empty($this->cookie) ) 
		{
			$this->cookie = Cookie::get();
		}
		if( is_array($name) ) 
		{
			return $this->cookie = array_merge($this->cookie, $name);
		}
		if( !empty($name) ) 
		{
			$data = (Cookie::has($name) ? Cookie::get($name) : $default);
		}
		else 
		{
			$data = $this->cookie;
		}
		$filter = $this->getFilter($filter, $default);
		if( is_array($data) ) 
		{
			array_walk_recursive($data, array( $this, "filterValue" ), $filter);
			reset($data);
		}
		else 
		{
			$this->filterValue($data, $name, $filter);
		}
		return $data;
	}
	public function server($name = "", $default = NULL, $filter = "") 
	{
		if( empty($this->server) ) 
		{
			$this->server = $_SERVER;
		}
		if( is_array($name) ) 
		{
			return $this->server = array_merge($this->server, $name);
		}
		return $this->input($this->server, (false === $name ? false : strtoupper($name)), $default, $filter);
	}
	public function file($name = "") 
	{
		if( empty($this->file) ) 
		{
			$this->file = (isset($_FILES) ? $_FILES : array( ));
		}
		if( is_array($name) ) 
		{
			return $this->file = array_merge($this->file, $name);
		}
		$files = $this->file;
		if( !empty($files) ) 
		{
			$array = array( );
			foreach( $files as $key => $file ) 
			{
				if( is_array($file["name"]) ) 
				{
					$item = array( );
					$keys = array_keys($file);
					$count = count($file["name"]);
					for( $i = 0; $i < $count; $i++ ) 
					{
						if( empty($file["tmp_name"][$i]) || !is_file($file["tmp_name"][$i]) ) 
						{
							continue;
						}
						$temp["key"] = $key;
						foreach( $keys as $_key ) 
						{
							$temp[$_key] = $file[$_key][$i];
						}
						$item[] = (new File($temp["tmp_name"]))->setUploadInfo($temp);
					}
					$array[$key] = $item;
				}
				else 
				{
					if( $file instanceof File ) 
					{
						$array[$key] = $file;
					}
					else 
					{
						if( empty($file["tmp_name"]) || !is_file($file["tmp_name"]) ) 
						{
							continue;
						}
						$array[$key] = (new File($file["tmp_name"]))->setUploadInfo($file);
					}
				}
			}
			if( strpos($name, ".") ) 
			{
				list($name, $sub) = explode(".", $name);
			}
			if( "" === $name ) 
			{
				return $array;
			}
			if( isset($sub) && isset($array[$name][$sub]) ) 
			{
				return $array[$name][$sub];
			}
			if( isset($array[$name]) ) 
			{
				return $array[$name];
			}
		}
	}
	public function env($name = "", $default = NULL, $filter = "") 
	{
		if( empty($this->env) ) 
		{
			$this->env = $_ENV;
		}
		if( is_array($name) ) 
		{
			return $this->env = array_merge($this->env, $name);
		}
		return $this->input($this->env, (false === $name ? false : strtoupper($name)), $default, $filter);
	}
	public function header($name = "", $default = NULL) 
	{
		if( empty($this->header) ) 
		{
			$header = array( );
			if( function_exists("apache_request_headers") && ($result = apache_request_headers()) ) 
			{
				$header = $result;
			}
			else 
			{
				$server = ($this->server ?: $_SERVER);
				foreach( $server as $key => $val ) 
				{
					if( 0 === strpos($key, "HTTP_") ) 
					{
						$key = str_replace("_", "-", strtolower(substr($key, 5)));
						$header[$key] = $val;
					}
				}
				if( isset($server["CONTENT_TYPE"]) ) 
				{
					$header["content-type"] = $server["CONTENT_TYPE"];
				}
				if( isset($server["CONTENT_LENGTH"]) ) 
				{
					$header["content-length"] = $server["CONTENT_LENGTH"];
				}
			}
			$this->header = array_change_key_case($header);
		}
		if( is_array($name) ) 
		{
			return $this->header = array_merge($this->header, $name);
		}
		if( "" === $name ) 
		{
			return $this->header;
		}
		$name = str_replace("_", "-", strtolower($name));
		return (isset($this->header[$name]) ? $this->header[$name] : $default);
	}
	public function input($data = array( ), $name = "", $default = NULL, $filter = "") 
	{
		if( false === $name ) 
		{
			return $data;
		}
		$name = (string) $name;
		if( "" != $name ) 
		{
			if( strpos($name, "/") ) 
			{
				list($name, $type) = explode("/", $name);
			}
			else 
			{
				$type = "s";
			}
			foreach( explode(".", $name) as $val ) 
			{
				if( isset($data[$val]) ) 
				{
					$data = $data[$val];
				}
				else 
				{
					return $default;
				}
			}
			if( is_object($data) ) 
			{
				return $data;
			}
		}
		$filter = $this->getFilter($filter, $default);
		if( is_array($data) ) 
		{
			array_walk_recursive($data, array( $this, "filterValue" ), $filter);
			reset($data);
		}
		else 
		{
			$this->filterValue($data, $name, $filter);
		}
		if( isset($type) && $data !== $default ) 
		{
			$this->typeCast($data, $type);
		}
		return $data;
	}
	public function filter($filter = NULL) 
	{
		if( is_null($filter) ) 
		{
			return $this->filter;
		}
		$this->filter = $filter;
	}
	protected function getFilter($filter, $default) 
	{
		if( is_null($filter) ) 
		{
			$filter = array( );
		}
		else 
		{
			$filter = ($filter ?: $this->filter);
			if( is_string($filter) && false === strpos($filter, "/") ) 
			{
				$filter = explode(",", $filter);
			}
			else 
			{
				$filter = (array) $filter;
			}
		}
		$filter[] = $default;
		return $filter;
	}
	private function filterValue(&$value, $key, $filters) 
	{
		$default = array_pop($filters);
		foreach( $filters as $filter ) 
		{
			if( is_callable($filter) ) 
			{
				$value = call_user_func($filter, $value);
			}
			else 
			{
				if( is_scalar($value) ) 
				{
					if( false !== strpos($filter, "/") ) 
					{
						if( !preg_match($filter, $value) ) 
						{
							$value = $default;
							break;
						}
					}
					else 
					{
						if( !empty($filter) ) 
						{
							$value = filter_var($value, (is_int($filter) ? $filter : filter_id($filter)));
							if( false === $value ) 
							{
								$value = $default;
								break;
							}
						}
					}
				}
			}
		}
		return $this->filterExp($value);
	}
	public function filterExp(&$value) 
	{
		if( is_string($value) && preg_match("/^(EXP|NEQ|GT|EGT|LT|ELT|OR|XOR|LIKE|NOTLIKE|NOT LIKE|NOT BETWEEN|NOTBETWEEN|BETWEEN|NOT EXISTS|NOTEXISTS|EXISTS|NOT NULL|NOTNULL|NULL|BETWEEN TIME|NOT BETWEEN TIME|NOTBETWEEN TIME|NOTIN|NOT IN|IN)\$/i", $value) ) 
		{
			$value .= " ";
		}
	}
	private function typeCast(&$data, $type) 
	{
		switch( strtolower($type) ) 
		{
			case "a": $data = (array) $data;
			break;
			case "d": $data = (int) $data;
			break;
			case "f": $data = (double) $data;
			break;
			case "b": $data = (bool) $data;
			break;
			case "s": default: if( is_scalar($data) ) 
			{
				$data = (string) $data;
			}
			else 
			{
				throw new \InvalidArgumentException("variable type error：" . gettype($data));
			}
		}
	}
	public function has($name, $type = "param", $checkEmpty = false) 
	{
		if( empty($this->$type) ) 
		{
			$param = $this->$type();
		}
		else 
		{
			$param = $this->$type;
		}
		foreach( explode(".", $name) as $val ) 
		{
			if( isset($param[$val]) ) 
			{
				$param = $param[$val];
			}
			else 
			{
				return false;
			}
		}
		return ($checkEmpty && "" === $param ? false : true);
	}
	public function only($name, $type = "param") 
	{
		$param = $this->$type();
		if( is_string($name) ) 
		{
			$name = explode(",", $name);
		}
		$item = array( );
		foreach( $name as $key ) 
		{
			if( isset($param[$key]) ) 
			{
				$item[$key] = $param[$key];
			}
		}
		return $item;
	}
	public function except($name, $type = "param") 
	{
		$param = $this->$type();
		if( is_string($name) ) 
		{
			$name = explode(",", $name);
		}
		foreach( $name as $key ) 
		{
			if( isset($param[$key]) ) 
			{
				unset($param[$key]);
			}
		}
		return $param;
	}
	public function isSsl() 
	{
		$server = array_merge($_SERVER, $this->server);
		if( isset($server["HTTPS"]) && ("1" == $server["HTTPS"] || "on" == strtolower($server["HTTPS"])) ) 
		{
			return true;
		}
		if( isset($server["REQUEST_SCHEME"]) && "https" == $server["REQUEST_SCHEME"] ) 
		{
			return true;
		}
		if( isset($server["SERVER_PORT"]) && "443" == $server["SERVER_PORT"] ) 
		{
			return true;
		}
		if( isset($server["HTTP_X_FORWARDED_PROTO"]) && "https" == $server["HTTP_X_FORWARDED_PROTO"] ) 
		{
			return true;
		}
		if( Config::get("https_agent_name") && isset($server[Config::get("https_agent_name")]) ) 
		{
			return true;
		}
		return false;
	}
	public function isAjax($ajax = false) 
	{
		$value = $this->server("HTTP_X_REQUESTED_WITH", "", "strtolower");
		$result = ("xmlhttprequest" == $value ? true : false);
		if( true === $ajax ) 
		{
			return $result;
		}
		$result = ($this->param(Config::get("var_ajax")) ? true : $result);
		$this->mergeParam = false;
		return $result;
	}
	public function isPjax($pjax = false) 
	{
		$result = (!is_null($this->server("HTTP_X_PJAX")) ? true : false);
		if( true === $pjax ) 
		{
			return $result;
		}
		$result = ($this->param(Config::get("var_pjax")) ? true : $result);
		$this->mergeParam = false;
		return $result;
	}
	public function ip($type = 0, $adv = true) 
	{
		$type = ($type ? 1 : 0);
		static $ip = NULL;
		if( null !== $ip ) 
		{
			return $ip[$type];
		}
		$httpAgentIp = Config::get("http_agent_ip");
		if( $httpAgentIp && isset($_SERVER[$httpAgentIp]) ) 
		{
			$ip = $_SERVER[$httpAgentIp];
		}
		else 
		{
			if( $adv ) 
			{
				if( isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ) 
				{
					$arr = explode(",", $_SERVER["HTTP_X_FORWARDED_FOR"]);
					$pos = array_search("unknown", $arr);
					if( false !== $pos ) 
					{
						unset($arr[$pos]);
					}
					$ip = trim(current($arr));
				}
				else 
				{
					if( isset($_SERVER["HTTP_CLIENT_IP"]) ) 
					{
						$ip = $_SERVER["HTTP_CLIENT_IP"];
					}
					else 
					{
						if( isset($_SERVER["REMOTE_ADDR"]) ) 
						{
							$ip = $_SERVER["REMOTE_ADDR"];
						}
					}
				}
			}
			else 
			{
				if( isset($_SERVER["REMOTE_ADDR"]) ) 
				{
					$ip = $_SERVER["REMOTE_ADDR"];
				}
			}
		}
		$long = sprintf("%u", ip2long($ip));
		$ip = ($long ? array( $ip, $long ) : array( "0.0.0.0", 0 ));
		return $ip[$type];
	}
	public function isMobile() 
	{
		if( isset($_SERVER["HTTP_VIA"]) && stristr($_SERVER["HTTP_VIA"], "wap") ) 
		{
			return true;
		}
		if( isset($_SERVER["HTTP_ACCEPT"]) && strpos(strtoupper($_SERVER["HTTP_ACCEPT"]), "VND.WAP.WML") ) 
		{
			return true;
		}
		if( isset($_SERVER["HTTP_X_WAP_PROFILE"]) || isset($_SERVER["HTTP_PROFILE"]) ) 
		{
			return true;
		}
		if( isset($_SERVER["HTTP_USER_AGENT"]) && preg_match("/(blackberry|configuration\\/cldc|hp |hp-|htc |htc_|htc-|iemobile|kindle|midp|mmp|motorola|mobile|nokia|opera mini|opera |Googlebot-Mobile|YahooSeeker\\/M1A1-R2D2|android|iphone|ipod|mobi|palm|palmos|pocket|portalmmm|ppc;|smartphone|sonyericsson|sqh|spv|symbian|treo|up.browser|up.link|vodafone|windows ce|xda |xda_)/i", $_SERVER["HTTP_USER_AGENT"]) ) 
		{
			return true;
		}
		return false;
	}
	public function scheme() 
	{
		return ($this->isSsl() ? "https" : "http");
	}
	public function query() 
	{
		return $this->server("QUERY_STRING");
	}
	public function host($strict = false) 
	{
		if( isset($_SERVER["HTTP_X_REAL_HOST"]) ) 
		{
			$host = $_SERVER["HTTP_X_REAL_HOST"];
		}
		else 
		{
			$host = $this->server("HTTP_HOST");
		}
		return (true === $strict && strpos($host, ":") ? strstr($host, ":", true) : $host);
	}
	public function port() 
	{
		return $this->server("SERVER_PORT");
	}
	public function protocol() 
	{
		return $this->server("SERVER_PROTOCOL");
	}
	public function remotePort() 
	{
		return $this->server("REMOTE_PORT");
	}
	public function contentType() 
	{
		$contentType = $this->server("CONTENT_TYPE");
		if( $contentType ) 
		{
			if( strpos($contentType, ";") ) 
			{
				list($type) = explode(";", $contentType);
			}
			else 
			{
				$type = $contentType;
			}
			return trim($type);
		}
		return "";
	}
	public function routeInfo($route = array( )) 
	{
		if( !empty($route) ) 
		{
			$this->routeInfo = $route;
		}
		else 
		{
			return $this->routeInfo;
		}
	}
	public function dispatch($dispatch = NULL) 
	{
		if( !is_null($dispatch) ) 
		{
			$this->dispatch = $dispatch;
		}
		return $this->dispatch;
	}
	public function module($module = NULL) 
	{
		if( !is_null($module) ) 
		{
			$this->module = $module;
			return $this;
		}
		return ($this->module ?: "");
	}
	public function controller($controller = NULL) 
	{
		if( !is_null($controller) ) 
		{
			$this->controller = $controller;
			return $this;
		}
		return ($this->controller ?: "");
	}
	public function action($action = NULL) 
	{
		if( !is_null($action) && !is_bool($action) ) 
		{
			$this->action = $action;
			return $this;
		}
		$name = ($this->action ?: "");
		return (true === $action ? $name : strtolower($name));
	}
	public function langset($lang = NULL) 
	{
		if( !is_null($lang) ) 
		{
			$this->langset = $lang;
			return $this;
		}
		return ($this->langset ?: "");
	}
	public function getContent() 
	{
		if( is_null($this->content) ) 
		{
			$this->content = $this->input;
		}
		return $this->content;
	}
	public function getInput() 
	{
		return $this->input;
	}
	public function token($name = "__token__", $type = "md5") 
	{
		$type = (is_callable($type) ? $type : "md5");
		$token = call_user_func($type, $_SERVER["REQUEST_TIME_FLOAT"]);
		if( $this->isAjax() ) 
		{
			header($name . ": " . $token);
		}
		Session::set($name, $token);
		return $token;
	}
	public function cache($key, $expire = NULL, $except = array( ), $tag = NULL) 
	{
		if( !is_array($except) ) 
		{
			$tag = $except;
			$except = array( );
		}
		if( false !== $key && $this->isGet() && !$this->isCheckCache ) 
		{
			$this->isCheckCache = true;
			if( false === $expire ) 
			{
				return NULL;
			}
			if( $key instanceof \Closure ) 
			{
				$key = call_user_func_array($key, array( $this ));
			}
			else 
			{
				if( true === $key ) 
				{
					foreach( $except as $rule ) 
					{
						if( 0 === stripos($this->url(), $rule) ) 
						{
							return NULL;
						}
					}
					$key = "__URL__";
				}
				else 
				{
					if( strpos($key, "|") ) 
					{
						list($key, $fun) = explode("|", $key);
					}
				}
			}
			if( false !== strpos($key, "__") ) 
			{
				$key = str_replace(array( "__MODULE__", "__CONTROLLER__", "__ACTION__", "__URL__", "" ), array( $this->module, $this->controller, $this->action, md5($this->url(true)) ), $key);
			}
			if( false !== strpos($key, ":") ) 
			{
				$param = $this->param();
				foreach( $param as $item => $val ) 
				{
					if( is_string($val) && false !== strpos($key, ":" . $item) ) 
					{
						$key = str_replace(":" . $item, $val, $key);
					}
				}
			}
			else 
			{
				if( strpos($key, "]") ) 
				{
					if( "[" . $this->ext() . "]" == $key ) 
					{
						$key = md5($this->url());
					}
					else 
					{
						return NULL;
					}
				}
			}
			if( isset($fun) ) 
			{
				$key = $fun($key);
			}
			if( $_SERVER["REQUEST_TIME"] < strtotime($this->server("HTTP_IF_MODIFIED_SINCE")) + $expire ) 
			{
				$response = Response::create()->code(304);
				throw new exception\HttpResponseException($response);
			}
			if( Cache::has($key) ) 
			{
				list($content, $header) = Cache::get($key);
				$response = Response::create($content)->header($header);
				throw new exception\HttpResponseException($response);
			}
			$this->cache = array( $key, $expire, $tag );
		}
	}
	public function getCache() 
	{
		return $this->cache;
	}
	public function bind($name, $obj = NULL) 
	{
		if( is_array($name) ) 
		{
			$this->bind = array_merge($this->bind, $name);
		}
		else 
		{
			$this->bind[$name] = $obj;
		}
	}
	public function __set($name, $value) 
	{
		$this->bind[$name] = $value;
	}
	public function __get($name) 
	{
		return (isset($this->bind[$name]) ? $this->bind[$name] : null);
	}
	public function __isset($name) 
	{
		return isset($this->bind[$name]);
	}
}
?>