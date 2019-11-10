<?php  namespace Guzzle\Cache;
class Zf1CacheAdapter extends AbstractCacheAdapter 
{
	public function __construct(\Zend_Cache_Backend $cache) 
	{
		\Guzzle\Common\Version::warn("Guzzle\\Cache\\Zf1CacheAdapter" . " is deprecated. Upgrade to ZF2 or use PsrCacheAdapter");
		$this->cache = $cache;
	}
	public function contains($id, array $options = NULL) 
	{
		return $this->cache->test($id);
	}
	public function delete($id, array $options = NULL) 
	{
		return $this->cache->remove($id);
	}
	public function fetch($id, array $options = NULL) 
	{
		return $this->cache->load($id);
	}
	public function save($id, $data, $lifeTime = false, array $options = NULL) 
	{
		return $this->cache->save($data, $id, array( ), $lifeTime);
	}
}
?>