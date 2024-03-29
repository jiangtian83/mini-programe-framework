<?php  namespace Guzzle\Tests\Http\Curl;
class CurlVersionTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testCachesCurlInfo() 
	{
		$info = curl_version();
		$instance = \Guzzle\Http\Curl\CurlVersion::getInstance();
		$refObject = new \ReflectionObject($instance);
		$refProperty = $refObject->getProperty("version");
		$refProperty->setAccessible(true);
		$refProperty->setValue($instance, array( ));
		$this->assertEquals($info, $instance->getAll());
		$this->assertEquals($info, $instance->getAll());
		$this->assertEquals($info["version"], $instance->get("version"));
		$this->assertFalse($instance->get("foo"));
	}
	public function testIsSingleton() 
	{
		$refObject = new \ReflectionClass("Guzzle\\Http\\Curl\\CurlVersion");
		$refProperty = $refObject->getProperty("instance");
		$refProperty->setAccessible(true);
		$refProperty->setValue(null, null);
		$this->assertInstanceOf("Guzzle\\Http\\Curl\\CurlVersion", \Guzzle\Http\Curl\CurlVersion::getInstance());
	}
}
?>