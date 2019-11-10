<?php  namespace Guzzle\Parser\Url;
class UrlParser implements UrlParserInterface 
{
	protected $utf8 = false;
	public function setUtf8Support($utf8) 
	{
		$this->utf8 = $utf8;
	}
	public function parseUrl($url) 
	{
		\Guzzle\Common\Version::warn("Guzzle\\Parser\\Url\\UrlParser" . " is deprecated. Just use parse_url()");
		static $defaults = array( "scheme" => NULL, "host" => NULL, "path" => NULL, "port" => NULL, "query" => NULL, "user" => NULL, "pass" => NULL, "fragment" => NULL );
		$parts = parse_url($url);
		if( $this->utf8 && isset($parts["query"]) ) 
		{
			$queryPos = strpos($url, "?");
			if( isset($parts["fragment"]) ) 
			{
				$parts["query"] = substr($url, $queryPos + 1, strpos($url, "#") - $queryPos - 1);
			}
			else 
			{
				$parts["query"] = substr($url, $queryPos + 1);
			}
		}
		return $parts + $defaults;
	}
}
?>