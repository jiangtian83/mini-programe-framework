<?php  namespace Guzzle\Tests\Service;
class CachingConfigLoaderTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testLoadsPhpFileIncludes() 
	{
		$cache = new \Guzzle\Cache\DoctrineCacheAdapter(new \Doctrine\Common\Cache\ArrayCache());
		$loader = $this->getMockBuilder("Guzzle\\Service\\ConfigLoaderInterface")->setMethods(array( "load" ))->getMockForAbstractClass();
		$data = array( "foo" => "bar" );
		$loader->expects($this->once())->method("load")->will($this->returnValue($data));
		$cache = new \Guzzle\Service\CachingConfigLoader($loader, $cache);
		$this->assertEquals($data, $cache->load("foo"));
		$this->assertEquals($data, $cache->load("foo"));
	}
	public function testDoesNotCacheArrays() 
	{
		$cache = new \Guzzle\Cache\DoctrineCacheAdapter(new \Doctrine\Common\Cache\ArrayCache());
		$loader = $this->getMockBuilder("Guzzle\\Service\\ConfigLoaderInterface")->setMethods(array( "load" ))->getMockForAbstractClass();
		$data = array( "foo" => "bar" );
		$loader->expects($this->exactly(2))->method("load")->will($this->returnValue($data));
		$cache = new \Guzzle\Service\CachingConfigLoader($loader, $cache);
		$this->assertEquals($data, $cache->load(array( )));
		$this->assertEquals($data, $cache->load(array( )));
	}
}
?>