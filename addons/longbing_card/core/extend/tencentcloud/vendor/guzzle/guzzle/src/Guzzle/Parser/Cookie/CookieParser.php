<?php  namespace Guzzle\Parser\Cookie;
class CookieParser implements CookieParserInterface 
{
	protected static $cookieParts = array( "domain" => "Domain", "path" => "Path", "max_age" => "Max-Age", "expires" => "Expires", "version" => "Version", "secure" => "Secure", "port" => "Port", "discard" => "Discard", "comment" => "Comment", "comment_url" => "Comment-Url", "http_only" => "HttpOnly" );
	public function parseCookie($cookie, $host = NULL, $path = NULL, $decode = false) 
	{
		$pieces = array_filter(array_map("trim", explode(";", $cookie)));
		if( empty($pieces) || !strpos($pieces[0], "=") ) 
		{
			return false;
		}
		$data = array_merge(array_fill_keys(array_keys(self::$cookieParts), null), array( "cookies" => array( ), "data" => array( ), "path" => null, "http_only" => false, "discard" => false, "domain" => $host ));
		$foundNonCookies = 0;
		foreach( $pieces as $part ) 
		{
			$cookieParts = explode("=", $part, 2);
			$key = trim($cookieParts[0]);
			if( count($cookieParts) == 1 ) 
			{
				$value = true;
			}
			else 
			{
				$value = trim($cookieParts[1], " \n\r\t");
				if( $decode ) 
				{
					$value = urldecode($value);
				}
			}
			if( !empty($data["cookies"]) ) 
			{
				foreach( self::$cookieParts as $mapValue => $search ) 
				{
					if( !strcasecmp($search, $key) ) 
					{
						$data[$mapValue] = ($mapValue == "port" ? array_map("trim", explode(",", $value)) : $value);
						$foundNonCookies++;
						continue 2;
					}
				}
			}
			$data[($foundNonCookies ? "data" : "cookies")][$key] = $value;
		}
		if( !$data["expires"] && $data["max_age"] ) 
		{
			$data["expires"] = time() + (int) $data["max_age"];
		}
		if( !$data["path"] || substr($data["path"], 0, 1) !== "/" ) 
		{
			$data["path"] = $this->getDefaultPath($path);
		}
		return $data;
	}
	protected function getDefaultPath($path) 
	{
		if( empty($path) || substr($path, 0, 1) !== "/" ) 
		{
			return "/";
		}
		if( $path === "/" ) 
		{
			return $path;
		}
		$rightSlashPos = strrpos($path, "/");
		if( $rightSlashPos === 0 ) 
		{
			return "/";
		}
		return substr($path, 0, $rightSlashPos);
	}
}
?>