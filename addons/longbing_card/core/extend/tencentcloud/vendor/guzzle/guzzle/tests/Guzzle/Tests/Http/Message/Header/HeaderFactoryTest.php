<?php  namespace Guzzle\Tests\Http\Message\Header;
class HeaderFactoryTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testCreatesBasicHeaders() 
	{
		$f = new \Guzzle\Http\Message\Header\HeaderFactory();
		$h = $f->createHeader("Foo", "Bar");
		$this->assertInstanceOf("Guzzle\\Http\\Message\\Header", $h);
		$this->assertEquals("Foo", $h->getName());
		$this->assertEquals("Bar", (string) $h);
	}
	public function testCreatesSpecificHeaders() 
	{
		$f = new \Guzzle\Http\Message\Header\HeaderFactory();
		$h = $f->createHeader("Link", "<http>; rel=\"test\"");
		$this->assertInstanceOf("Guzzle\\Http\\Message\\Header\\Link", $h);
		$this->assertEquals("Link", $h->getName());
		$this->assertEquals("<http>; rel=\"test\"", (string) $h);
	}
}
?>