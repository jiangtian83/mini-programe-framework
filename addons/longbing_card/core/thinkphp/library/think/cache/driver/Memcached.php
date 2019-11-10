<?php  namespace think\cache\driver;
class Memcached extends \think\cache\Driver 
{
	protected $options = array( "host" => "127.0.0.1", "port" => 11211, "expire" => 0, "timeout" => 0, "prefix" => "", "username" => "", "password" => "", "option" => array( ) );
	public function __construct($options = array( )) 
	{
		if( !extension_loaded("memcached") ) 
		{
			throw new \BadFunctionCallException("not support: memcached");
		}
		if( !empty($options) ) 
		{
			$this->options = array_merge($this->options, $options);
		}
		$this->handler = new \Memcached();
		if( !empty($this->options["option"]) ) 
		{
			$this->handler->setOptions($this->options["option"]);
		}
		if( 0 < $this->options["timeout"] ) 
		{
			$this->handler->setOption(\Memcached::OPT_CONNECT_TIMEOUT, $this->options["timeout"]);
		}
		$hosts = explode(",", $this->options["host"]);
		$ports = explode(",", $this->options["port"]);
		if( empty($ports[0]) ) 
		{
			$ports[0] = 11211;
		}
		$servers = array( );
		foreach( (array) $hosts as $i => $host ) 
		{
			$servers[] = array( $host, (isset($ports[$i]) ? $ports[$i] : $ports[0]), 1 );
		}
		$this->handler->addServers($servers);
		if( "" != $this->options["username"] ) 
		{
			$this->handler->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);
			$this->handler->setSaslAuthData($this->options["username"], $this->options["password"]);
		}
	}
	public function has($name) 
	{
		$key = $this->getCacheKey($name);
		return ($this->handler->get($key) ? true : false);
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
		$expire = (0 == $expire ? 0 : $_SERVER["REQUEST_TIME"] + $expire);
		if( $this->handler->set($key, $value, $expire) ) 
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
			$this->handler->deleteMulti($keys);
			$this->rm("tag_" . md5($tag));
			return true;
		}
		return $this->handler->flush();
	}
}
?>