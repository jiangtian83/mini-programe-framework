<?php  namespace Guzzle\Tests\Cache;
class ClosureCacheAdapterTest extends \Guzzle\Tests\GuzzleTestCase 
{
	private $adapter = NULL;
	private $callables = NULL;
	public $data = array( );
	protected function setUp() 
	{
		parent::setUp();
		$that = $this;
		$this->callables = array( "contains" => function($id, $options = array( )) use ($that) 
		{
			return array_key_exists($id, $that->data);
		}
		, "delete" => function($id, $options = array( )) use ($that) 
		{
			unset($that->data[$id]);
			return true;
		}
		, "fetch" => function($id, $options = array( )) use ($that) 
		{
			return (array_key_exists($id, $that->data) ? $that->data[$id] : null);
		}
		, "save" => function($id, $data, $lifeTime, $options = array( )) use ($that) 
		{
			$that->data[$id] = $data;
			return true;
		}
		);
		$this->adapter = new \Guzzle\Cache\ClosureCacheAdapter($this->callables);
	}
	protected function tearDown() 
	{
		$this->cache = null;
		$this->callables = null;
		parent::tearDown();
	}
	public function testEnsuresCallablesArePresent() 
	{
		$callables = $this->callables;
		unset($callables["delete"]);
		$cache = new \Guzzle\Cache\ClosureCacheAdapter($callables);
	}
	public function testAllCallablesMustBePresent() 
	{
		$cache = new \Guzzle\Cache\ClosureCacheAdapter($this->callables);
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