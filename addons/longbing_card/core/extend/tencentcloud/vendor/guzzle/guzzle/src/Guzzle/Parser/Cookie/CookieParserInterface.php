<?php  namespace Guzzle\Parser\Cookie;
interface CookieParserInterface 
{
	public function parseCookie($cookie, $host, $path, $decode);
}
?>