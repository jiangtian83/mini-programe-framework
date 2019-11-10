<?php  namespace Guzzle\Tests\Cache;
class CacheAdapterFactoryTest extends \Guzzle\Tests\GuzzleTestCase 
{
	private $cache = NULL;
	private $adapter = NULL;
	protected function setup() 
	{
		parent::setUp();
		$this->cache = new \Doctrine\Common\Cache\ArrayCache();
		$this->adapter = new \Guzzle\Cache\DoctrineCacheAdapter($this->cache);
	}
	public function testEnsuresConfigIsObject() 
	{
		\Guzzle\Cache\CacheAdapterFactory::fromCache(array( ));
	}
	public function testEnsuresKnownType() 
	{
		\Guzzle\Cache\CacheAdapterFactory::fromCache(new \stdClass());
	}
	public function cacheProvider() 
	{
		return array( array( new \Guzzle\Cache\DoctrineCacheAdapter(new \Doctrine\Common\Cache\ArrayCache()), "Guzzle\\Cache\\DoctrineCacheAdapter" ), array( new \Doctrine\Common\Cache\ArrayCache(), "Guzzle\\Cache\\DoctrineCacheAdapter" ), array( \Zend\Cache\StorageFactory::factory(array( "adapter" => "memory" )), "Guzzle\\Cache\\Zf2CacheAdapter" ) );
	}
	public function testCreatesNullCacheAdapterByDefault($cache, $type) 
	{
		$adapter = \Guzzle\Cache\CacheAdapterFactory::fromCache($cache);
		$this->assertInstanceOf($type, $adapter);
	}
}
?>