<?php  namespace Guzzle\Cache;
class DoctrineCacheAdapter extends AbstractCacheAdapter 
{
	public function __construct(\Doctrine\Common\Cache\Cache $cache) 
	{
		$this->cache = $cache;
	}
	public function contains($id, array $options = NULL) 
	{
		return $this->cache->contains($id);
	}
	public function delete($id, array $options = NULL) 
	{
		return $this->cache->delete($id);
	}
	public function fetch($id, array $options = NULL) 
	{
		return $this->cache->fetch($id);
	}
	public function save($id, $data, $lifeTime = false, array $options = NULL) 
	{
		return $this->cache->save($id, $data, ($lifeTime !== false ? $lifeTime : 0));
	}
}
?>