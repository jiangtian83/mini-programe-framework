<?php  namespace Guzzle\Tests\Cache;
class Zf2CacheAdapterTest extends \Guzzle\Tests\GuzzleTestCase 
{
	private $cache = NULL;
	private $adapter = NULL;
	protected function setUp() 
	{
		parent::setUp();
		$this->cache = \Zend\Cache\StorageFactory::factory(array( "adapter" => "memory" ));
		$this->adapter = new \Guzzle\Cache\Zf2CacheAdapter($this->cache);
	}
	protected function tearDown() 
	{
		$this->adapter = null;
		$this->cache = null;
		parent::tearDown();
	}
	public function testCachesDataUsingCallables() 
	{
		$this->assertTrue($this->adapter->save("test", "data", 1000));
		$this->assertEquals("data", $this->adapter->fetch("test"));
	}
	public function testChecksIfCacheContainsKeys() 
	{
		$this->adapter->save("test", "data", 1000);
		$this->assertTrue($this->adapter->contains("test"));
		$this->assertFalse($this->adapter->contains("foo"));
	}
	public function testDeletesFromCacheByKey() 
	{
		$this->adapter->save("test", "data", 1000);
		$this->assertTrue($this->adapter->contains("test"));
		$this->adapter->delete("test");
		$this->assertFalse($this->adapter->contains("test"));
	}
}
?>