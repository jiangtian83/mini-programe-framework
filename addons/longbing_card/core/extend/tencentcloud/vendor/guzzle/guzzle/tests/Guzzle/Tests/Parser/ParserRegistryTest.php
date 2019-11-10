<?php  namespace Guzzle\Tests\Parser;
class ParserRegistryTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testStoresObjects() 
	{
		$r = new \Guzzle\Parser\ParserRegistry();
		$c = new \stdClass();
		$r->registerParser("foo", $c);
		$this->assertSame($c, $r->getParser("foo"));
	}
	public function testReturnsNullWhenNotFound() 
	{
		$r = new \Guzzle\Parser\ParserRegistry();
		$this->assertNull($r->getParser("FOO"));
	}
	public function testReturnsLazyLoadedDefault() 
	{
		$r = new \Guzzle\Parser\ParserRegistry();
		$c = $r->getParser("cookie");
		$this->assertInstanceOf("Guzzle\\Parser\\Cookie\\CookieParser", $c);
		$this->assertSame($c, $r->getParser("cookie"));
	}
}
?>