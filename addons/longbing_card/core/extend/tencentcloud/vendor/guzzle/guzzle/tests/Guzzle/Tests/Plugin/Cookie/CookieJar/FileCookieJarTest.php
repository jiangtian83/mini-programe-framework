<?php  namespace Guzzle\Tests\Plugin\Cookie\CookieJar;
class FileCookieJarTest extends \Guzzle\Tests\GuzzleTestCase 
{
	private $file = NULL;
	public function setUp() 
	{
		$this->file = tempnam("/tmp", "file-cookies");
	}
	public function testLoadsFromFileFile() 
	{
		$jar = new \Guzzle\Plugin\Cookie\CookieJar\FileCookieJar($this->file);
		$this->assertEquals(array( ), $jar->all());
		unlink($this->file);
	}
	public function testPersistsToFileFile() 
	{
		$jar = new \Guzzle\Plugin\Cookie\CookieJar\FileCookieJar($this->file);
		$jar->add(new \Guzzle\Plugin\Cookie\Cookie(array( "name" => "foo", "value" => "bar", "domain" => "foo.com", "expires" => time() + 1000 )));
		$jar->add(new \Guzzle\Plugin\Cookie\Cookie(array( "name" => "baz", "value" => "bar", "domain" => "foo.com", "expires" => time() + 1000 )));
		$jar->add(new \Guzzle\Plugin\Cookie\Cookie(array( "name" => "boo", "value" => "bar", "domain" => "foo.com" )));
		$this->assertEquals(3, count($jar));
		unset($jar);
		$contents = file_get_contents($this->file);
		$this->assertNotEmpty($contents);
		$jar = new \Guzzle\Plugin\Cookie\CookieJar\FileCookieJar($this->file);
		$this->assertEquals(2, count($jar));
		unset($jar);
		unlink($this->file);
	}
}
?>