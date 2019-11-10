<?php  namespace Guzzle\Cache;
class NullCacheAdapter extends AbstractCacheAdapter 
{
	public function __construct() 
	{
	}
	public function contains($id, array $options = NULL) 
	{
		return false;
	}
	public function delete($id, array $options = NULL) 
	{
		return true;
	}
	public function fetch($id, array $options = NULL) 
	{
		return false;
	}
	public function save($id, $data, $lifeTime = false, array $options = NULL) 
	{
		return true;
	}
}
?>