<?php  namespace Guzzle\Http;
class Url 
{
	protected $scheme = NULL;
	protected $host = NULL;
	protected $port = NULL;
	protected $username = NULL;
	protected $password = NULL;
	protected $path = "";
	protected $fragment = NULL;
	protected $query = NULL;
	public static function factory($url) 
	{
		static $defaults = array( "scheme" => NULL, "host" => NULL, "path" => NULL, "port" => NULL, "query" => NULL, "user" => NULL, "pass" => NULL, "fragment" => NULL );
		if( false === ($parts = parse_url($url)) ) 
		{
			throw new \Guzzle\Common\Exception\InvalidArgumentException("Was unable to parse malformed url: " . $url);
		}
		$parts += $defaults;
		if( $parts["query"] || 0 !== strlen($parts["query"]) ) 
		{
			$parts["query"] = QueryString::fromString($parts["query"]);
		}
		return new static($parts["scheme"], $parts["host"], $parts["user"], $parts["pass"], $parts["port"], $parts["path"], $parts["query"], $parts["fragment"]);
	}
	public static function buildUrl(array $parts) 
	{
		$url = $scheme = "";
		if( isset($parts["scheme"]) ) 
		{
			$scheme = $parts["scheme"];
			$url .= $scheme . ":";
		}
		if( isset($parts["host"]) ) 
		{
			$url .= "//";
			if( isset($parts["user"]) ) 
			{
				$url .= $parts["user"];
				if( isset($parts["pass"]) ) 
				{
					$url .= ":" . $parts["pass"];
				}
				$url .= "@";
			}
			$url .= $parts["host"];
			if( isset($parts["port"]) && !($scheme == "http" && $parts["port"] == 80 || $scheme == "https" && $parts["port"] == 443) ) 
			{
				$url .= ":" . $parts["port"];
			}
		}
		if( isset($parts["path"]) && 0 !== strlen($parts["path"]) ) 
		{
			if( $url && $parts["path"][0] != "/" && substr($url, -1) != "/" ) 
			{
				$url .= "/";
			}
			$url .= $parts["path"];
		}
		if( isset($parts["query"]) ) 
		{
			$url .= "?" . $parts["query"];
		}
		if( isset($parts["fragment"]) ) 
		{
			$url .= "#" . $parts["fragment"];
		}
		return $url;
	}
	public function __construct($scheme, $host, $username = NULL, $password = NULL, $port = NULL, $path = NULL, QueryString $query = NULL, $fragment = NULL) 
	{
		$this->scheme = $scheme;
		$this->host = $host;
		$this->port = $port;
		$this->username = $username;
		$this->password = $password;
		$this->fragment = $fragment;
		if( !$query ) 
		{
			$this->query = new QueryString();
		}
		else 
		{
			$this->setQuery($query);
		}
		$this->setPath($path);
	}
	public function __clone() 
	{
		$this->query = clone $this->query;
	}
	public function __toString() 
	{
		return self::buildUrl($this->getParts());
	}
	public function getParts() 
	{
		$query = (string) $this->query;
		return array( "scheme" => $this->scheme, "user" => $this->username, "pass" => $this->password, "host" => $this->host, "port" => $this->port, "path" => $this->getPath(), "query" => ($query !== "" ? $query : null), "fragment" => $this->fragment );
	}
	public function setHost($host) 
	{
		if( strpos($host, ":") === false ) 
		{
			$this->host = $host;
		}
		else 
		{
			list($host, $port) = explode(":", $host);
			$this->host = $host;
			$this->setPort($port);
		}
		return $this;
	}
	public function getHost() 
	{
		return $this->host;
	}
	public function setScheme($scheme) 
	{
		if( $this->scheme == "http" && $this->port == 80 ) 
		{
			$this->port = null;
		}
		else 
		{
			if( $this->scheme == "https" && $this->port == 443 ) 
			{
				$this->port = null;
			}
		}
		$this->scheme = $scheme;
		return $this;
	}
	public function getScheme() 
	{
		return $this->scheme;
	}
	public function setPort($port) 
	{
		$this->port = $port;
		return $this;
	}
	public function getPort() 
	{
		if( $this->port ) 
		{
			return $this->port;
		}
		if( $this->scheme == "http" ) 
		{
			return 80;
		}
		if( $this->scheme == "https" ) 
		{
			return 443;
		}
		return null;
	}
	public function setPath($path) 
	{
		static $pathReplace = array( " " => "%20", "?" => "%3F" );
		if( is_array($path) ) 
		{
			$path = "/" . implode("/", $path);
		}
		$this->path = strtr($path, $pathReplace);
		return $this;
	}
	public function normalizePath() 
	{
		if( !$this->path || $this->path == "/" || $this->path == "*" ) 
		{
			return $this;
		}
		$results = array( );
		$segments = $this->getPathSegments();
		foreach( $segments as $segment ) 
		{
			if( $segment == ".." ) 
			{
				array_pop($results);
			}
			else 
			{
				if( $segment != "." && $segment != "" ) 
				{
					$results[] = $segment;
				}
			}
		}
		$this->path = (($this->path[0] == "/" ? "/" : "")) . implode("/", $results);
		if( $this->path != "/" && end($segments) == "" ) 
		{
			$this->path .= "/";
		}
		return $this;
	}
	public function addPath($relativePath) 
	{
		if( $relativePath != "/" && is_string($relativePath) && 0 < strlen($relativePath) ) 
		{
			if( $relativePath[0] != "/" ) 
			{
				$relativePath = "/" . $relativePath;
			}
			$this->setPath(str_replace("//", "/", $this->path . $relativePath));
		}
		return $this;
	}
	public function getPath() 
	{
		return $this->path;
	}
	public function getPathSegments() 
	{
		return array_slice(explode("/", $this->getPath()), 1);
	}
	public function setPassword($password) 
	{
		$this->password = $password;
		return $this;
	}
	public function getPassword() 
	{
		return $this->password;
	}
	public function setUsername($username) 
	{
		$this->username = $username;
		return $this;
	}
	public function getUsername() 
	{
		return $this->username;
	}
	public function getQuery() 
	{
		return $this->query;
	}
	public function setQuery($query) 
	{
		if( is_string($query) ) 
		{
			$output = null;
			parse_str($query, $output);
			$this->query = new QueryString($output);
		}
		else 
		{
			if( is_array($query) ) 
			{
				$this->query = new QueryString($query);
			}
			else 
			{
				if( $query instanceof QueryString ) 
				{
					$this->query = $query;
				}
			}
		}
		return $this;
	}
	public function getFragment() 
	{
		return $this->fragment;
	}
	public function setFragment($fragment) 
	{
		$this->fragment = $fragment;
		return $this;
	}
	public function isAbsolute() 
	{
		return $this->scheme && $this->host;
	}
	public function combine($url, $strictRfc3986 = false) 
	{
		$url = self::factory($url);
		if( !$this->isAbsolute() && $url->isAbsolute() ) 
		{
			$url = $url->combine($this);
		}
		if( $buffer = $url->getScheme() ) 
		{
			$this->scheme = $buffer;
			$this->host = $url->getHost();
			$this->port = $url->getPort();
			$this->username = $url->getUsername();
			$this->password = $url->getPassword();
			$this->path = $url->getPath();
			$this->query = $url->getQuery();
			$this->fragment = $url->getFragment();
			return $this;
		}
		if( $buffer = $url->getHost() ) 
		{
			$this->host = $buffer;
			$this->port = $url->getPort();
			$this->username = $url->getUsername();
			$this->password = $url->getPassword();
			$this->path = $url->getPath();
			$this->query = $url->getQuery();
			$this->fragment = $url->getFragment();
			return $this;
		}
		$path = $url->getPath();
		$query = $url->getQuery();
		if( !$path ) 
		{
			if( count($query) ) 
			{
				$this->addQuery($query, $strictRfc3986);
			}
		}
		else 
		{
			if( $path[0] == "/" ) 
			{
				$this->path = $path;
			}
			else 
			{
				if( $strictRfc3986 ) 
				{
					$this->path .= "/../" . $path;
				}
				else 
				{
					$this->path .= "/" . $path;
				}
			}
			$this->normalizePath();
			$this->addQuery($query, $strictRfc3986);
		}
		$this->fragment = $url->getFragment();
		return $this;
	}
	private function addQuery(QueryString $new, $strictRfc386) 
	{
		if( !$strictRfc386 ) 
		{
			$new->merge($this->query);
		}
		$this->query = $new;
	}
}
?>