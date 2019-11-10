<?php  namespace Guzzle\Tests\Cache;
class CacheAdapterTest extends \Guzzle\Tests\GuzzleTestCase 
{
	private $cache = NULL;
	private $adapter = NULL;
	protected function setUp() 
	{
		parent::setUp();
		$this->cache = new \Doctrine\Common\Cache\ArrayCache();
		$this->adapter = new \Guzzle\Cache\DoctrineCacheAdapter($this->cache);
	}
	protected function tearDown() 
	{
		$this->adapter = null;
		$this->cache = null;
		parent::tearDown();
	}
	public function testGetCacheObject() 
	{
		$this->assertEquals($this->cache, $this->adapter->getCacheObject());
	}
	public function testSave() 
	{
		$this->assertTrue($this->adapter->save("test", "data", 1000));
	}
	public function testFetch() 
	{
		$this->assertTrue($this->adapter->save("test", "data", 1000));
		$this->assertEquals("data", $this->adapter->fetch("test"));
	}
	public function testContains() 
	{
		$this->assertTrue($this->adapter->save("test", "data", 1000));
		$this->assertTrue($this->adapter->contains("test"));
	}
	public function testDelete() 
	{
		$this->assertTrue($this->adapter->save("test", "data", 1000));
		$this->assertTrue($this->adapter->delete("test"));
		$this->assertFalse($this->adapter->contains("test"));
	}
}
?>