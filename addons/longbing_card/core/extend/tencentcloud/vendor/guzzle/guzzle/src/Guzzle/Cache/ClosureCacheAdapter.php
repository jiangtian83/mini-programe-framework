<?php  namespace Guzzle\Cache;
class ClosureCacheAdapter implements CacheAdapterInterface 
{
	protected $callables = NULL;
	public function __construct(array $callables) 
	{
		foreach( array( "contains", "delete", "fetch", "save" ) as $key ) 
		{
			if( !array_key_exists($key, $callables) || !is_callable($callables[$key]) ) 
			{
				throw new \InvalidArgumentException("callables must contain a callable " . $key . " key");
			}
		}
		$this->callables = $callables;
	}
	public function contains($id, array $options = NULL) 
	{
		return call_user_func($this->callables["contains"], $id, $options);
	}
	public function delete($id, array $options = NULL) 
	{
		return call_user_func($this->callables["delete"], $id, $options);
	}
	public function fetch($id, array $options = NULL) 
	{
		return call_user_func($this->callables["fetch"], $id, $options);
	}
	public function save($id, $data, $lifeTime = false, array $options = NULL) 
	{
		return call_user_func($this->callables["save"], $id, $data, $lifeTime, $options);
	}
}
?>