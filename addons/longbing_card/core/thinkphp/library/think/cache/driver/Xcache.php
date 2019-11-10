<?php  namespace think\cache\driver;
class Xcache extends \think\cache\Driver 
{
	protected $options = array( "prefix" => "", "expire" => 0 );
	public function __construct($options = array( )) 
	{
		if( !function_exists("xcache_info") ) 
		{
			throw new \BadFunctionCallException("not support: Xcache");
		}
		if( !empty($options) ) 
		{
			$this->options = array_merge($this->options, $options);
		}
	}
	public function has($name) 
	{
		$key = $this->getCacheKey($name);
		return xcache_isset($key);
	}
	public function get($name, $default = false) 
	{
		$key = $this->getCacheKey($name);
		return (xcache_isset($key) ? xcache_get($key) : $default);
	}
	public function set($name, $value, $expire = NULL) 
	{
		if( is_null($expire) ) 
		{
			$expire = $this->options["expire"];
		}
		if( $expire instanceof \DateTime ) 
		{
			$expire = $expire->getTimestamp() - time();
		}
		if( $this->tag && !$this->has($name) ) 
		{
			$first = true;
		}
		$key = $this->getCacheKey($name);
		if( xcache_set($key, $value, $expire) ) 
		{
			isset($first) and $this->setTagItem($key);
			return true;
		}
		return false;
	}
	public function inc($name, $step = 1) 
	{
		$key = $this->getCacheKey($name);
		return xcache_inc($key, $step);
	}
	public function dec($name, $step = 1) 
	{
		$key = $this->getCacheKey($name);
		return xcache_dec($key, $step);
	}
	public function rm($name) 
	{
		return xcache_unset($this->getCacheKey($name));
	}
	public function clear($tag = NULL) 
	{
		if( $tag ) 
		{
			$keys = $this->getTagItem($tag);
			foreach( $keys as $key ) 
			{
				xcache_unset($key);
			}
			$this->rm("tag_" . md5($tag));
			return true;
		}
		else 
		{
			if( function_exists("xcache_unset_by_prefix") ) 
			{
				return xcache_unset_by_prefix($this->options["prefix"]);
			}
			return false;
		}
	}
}
?>