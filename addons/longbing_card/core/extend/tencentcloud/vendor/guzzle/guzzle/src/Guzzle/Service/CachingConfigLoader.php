<?php  namespace Guzzle\Service;
class CachingConfigLoader implements ConfigLoaderInterface 
{
	protected $loader = NULL;
	protected $cache = NULL;
	public function __construct(ConfigLoaderInterface $loader, \Guzzle\Cache\CacheAdapterInterface $cache) 
	{
		$this->loader = $loader;
		$this->cache = $cache;
	}
	public function load($config, array $options = array( )) 
	{
		if( !is_string($config) ) 
		{
			$key = false;
		}
		else 
		{
			$key = "loader_" . crc32($config);
			if( $result = $this->cache->fetch($key) ) 
			{
				return $result;
			}
		}
		$result = $this->loader->load($config, $options);
		if( $key ) 
		{
			$this->cache->save($key, $result);
		}
		return $result;
	}
}
?>