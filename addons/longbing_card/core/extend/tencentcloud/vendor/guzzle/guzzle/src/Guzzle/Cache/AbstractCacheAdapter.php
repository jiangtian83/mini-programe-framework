<?php  namespace Guzzle\Cache;
abstract class AbstractCacheAdapter implements CacheAdapterInterface 
{
	protected $cache = NULL;
	public function getCacheObject() 
	{
		return $this->cache;
	}
}
?>