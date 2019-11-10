<?php  namespace Guzzle\Cache;
class Zf2CacheAdapter extends AbstractCacheAdapter 
{
	public function __construct(\Zend\Cache\Storage\StorageInterface $cache) 
	{
		$this->cache = $cache;
	}
	public function contains($id, array $options = NULL) 
	{
		return $this->cache->hasItem($id);
	}
	public function delete($id, array $options = NULL) 
	{
		return $this->cache->removeItem($id);
	}
	public function fetch($id, array $options = NULL) 
	{
		return $this->cache->getItem($id);
	}
	public function save($id, $data, $lifeTime = false, array $options = NULL) 
	{
		return $this->cache->setItem($id, $data);
	}
}
?>