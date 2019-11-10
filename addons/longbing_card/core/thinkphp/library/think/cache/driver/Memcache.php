<?php  namespace think\cache\driver;
class Memcache extends \think\cache\Driver 
{
	protected $options = array( "host" => "127.0.0.1", "port" => 11211, "expire" => 0, "timeout" => 0, "persistent" => true, "prefix" => "" );
	public function __construct($options = array( )) 
	{
		if( !extension_loaded("memcache") ) 
		{
			throw new \BadFunctionCallException("not support: memcache");
		}
		if( !empty($options) ) 
		{
			$this->options = array_merge($this->options, $options);
		}
		$this->handler = new \Memcache();
		$hosts = explode(",", $this->options["host"]);
		$ports = explode(",", $this->options["port"]);
		if( empty($ports[0]) ) 
		{
			$ports[0] = 11211;
		}
		foreach( (array) $hosts as $i => $host ) 
		{
			$port = (isset($ports[$i]) ? $ports[$i] : $ports[0]);
			(0 < $this->options["timeout"] ? $this->handler->addServer($host, $port, $this->options["persistent"], 1, $this->options["timeout"]) : $this->handler->addServer($host, $port, $this->options["persistent"], 1));
		}
	}
	public function has($name) 
	{
		$key = $this->getCacheKey($name);
		return false !== $this->handler->get($key);
	}
	public function get($name, $default = false) 
	{
		$result = $this->handler->get($this->getCacheKey($name));
		return (false !== $result ? $result : $default);
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
		if( $this->handler->set($key, $value, 0, $expire) ) 
		{
			isset($first) and $this->setTagItem($key);
			return true;
		}
		return false;
	}
	public function inc($name, $step = 1) 
	{
		$key = $this->getCacheKey($name);
		if( $this->handler->get($key) ) 
		{
			return $this->handler->increment($key, $step);
		}
		return $this->handler->set($key, $step);
	}
	public function dec($name, $step = 1) 
	{
		$key = $this->getCacheKey($name);
		$value = $this->handler->get($key) - $step;
		$res = $this->handler->set($key, $value);
		if( !$res ) 
		{
			return false;
		}
		return $value;
	}
	public function rm($name, $ttl = false) 
	{
		$key = $this->getCacheKey($name);
		return (false === $ttl ? $this->handler->delete($key) : $this->handler->delete($key, $ttl));
	}
	public function clear($tag = NULL) 
	{
		if( $tag ) 
		{
			$keys = $this->getTagItem($tag);
			foreach( $keys as $key ) 
			{
				$this->handler->delete($key);
			}
			$this->rm("tag_" . md5($tag));
			return true;
		}
		else 
		{
			return $this->handler->flush();
		}
	}
}
?>