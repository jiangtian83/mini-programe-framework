<?php  namespace Guzzle\Tests\Parser\Cookie;
class CookieParserTest extends CookieParserProvider 
{
	protected $cookieParserClass = "Guzzle\\Parser\\Cookie\\CookieParser";
	public function testUrlDecodesCookies() 
	{
		$parser = new \Guzzle\Parser\Cookie\CookieParser();
		$result = $parser->parseCookie("foo=baz+bar", null, null, true);
		$this->assertEquals(array( "foo" => "baz bar" ), $result["cookies"]);
	}
}
?>