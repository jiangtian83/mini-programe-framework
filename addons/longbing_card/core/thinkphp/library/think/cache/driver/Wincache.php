<?php  namespace think\cache\driver;
class Wincache extends \think\cache\Driver 
{
	protected $options = array( "prefix" => "", "expire" => 0 );
	public function __construct($options = array( )) 
	{
		if( !function_exists("wincache_ucache_info") ) 
		{
			throw new \BadFunctionCallException("not support: WinCache");
		}
		if( !empty($options) ) 
		{
			$this->options = array_merge($this->options, $options);
		}
	}
	public function has($name) 
	{
		$key = $this->getCacheKey($name);
		return wincache_ucache_exists($key);
	}
	public function get($name, $default = false) 
	{
		$key = $this->getCacheKey($name);
		return (wincache_ucache_exists($key) ? wincache_ucache_get($key) : $default);
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
		$key = $this->getCacheKey($name);
		if( $this->tag && !$this->has($name) ) 
		{
			$first = true;
		}
		if( wincache_ucache_set($key, $value, $expire) ) 
		{
			isset($first) and $this->setTagItem($key);
			return true;
		}
		return false;
	}
	public function inc($name, $step = 1) 
	{
		$key = $this->getCacheKey($name);
		return wincache_ucache_inc($key, $step);
	}
	public function dec($name, $step = 1) 
	{
		$key = $this->getCacheKey($name);
		return wincache_ucache_dec($key, $step);
	}
	public function rm($name) 
	{
		return wincache_ucache_delete($this->getCacheKey($name));
	}
	public function clear($tag = NULL) 
	{
		if( $tag ) 
		{
			$keys = $this->getTagItem($tag);
			foreach( $keys as $key ) 
			{
				wincache_ucache_delete($key);
			}
			$this->rm("tag_" . md5($tag));
			return true;
		}
		else 
		{
			return wincache_ucache_clear();
		}
	}
}
?>