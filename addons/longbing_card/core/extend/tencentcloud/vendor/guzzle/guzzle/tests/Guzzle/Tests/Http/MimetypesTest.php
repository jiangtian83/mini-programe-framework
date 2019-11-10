<?php  namespace Guzzle\Tests\Http;
class MimetypesTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testGetsFromExtension() 
	{
		$this->assertEquals("text/x-php", \Guzzle\Http\Mimetypes::getInstance()->fromExtension("php"));
	}
	public function testGetsFromFilename() 
	{
		$this->assertEquals("text/x-php", \Guzzle\Http\Mimetypes::getInstance()->fromFilename(__FILE__));
	}
	public function testGetsFromCaseInsensitiveFilename() 
	{
		$this->assertEquals("text/x-php", \Guzzle\Http\Mimetypes::getInstance()->fromFilename(strtoupper(__FILE__)));
	}
	public function testReturnsNullWhenNoMatchFound() 
	{
		$this->assertNull(\Guzzle\Http\Mimetypes::getInstance()->fromExtension("foobar"));
	}
}
?>