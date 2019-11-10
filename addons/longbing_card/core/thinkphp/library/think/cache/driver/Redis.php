<?php  namespace think\cache\driver;
class Redis extends \think\cache\Driver 
{
	protected $options = array( "host" => "127.0.0.1", "port" => 6379, "password" => "", "select" => 0, "timeout" => 0, "expire" => 0, "persistent" => false, "prefix" => "" );
	public function __construct($options = array( )) 
	{
		if( !extension_loaded("redis") ) 
		{
			throw new \BadFunctionCallException("not support: redis");
		}
		if( !empty($options) ) 
		{
			$this->options = array_merge($this->options, $options);
		}
		$this->handler = new \Redis();
		if( $this->options["persistent"] ) 
		{
			$this->handler->pconnect($this->options["host"], $this->options["port"], $this->options["timeout"], "persistent_id_" . $this->options["select"]);
		}
		else 
		{
			$this->handler->connect($this->options["host"], $this->options["port"], $this->options["timeout"]);
		}
		if( "" != $this->options["password"] ) 
		{
			$this->handler->auth($this->options["password"]);
		}
		if( 0 != $this->options["select"] ) 
		{
			$this->handler->select($this->options["select"]);
		}
	}
	public function has($name) 
	{
		return $this->handler->exists($this->getCacheKey($name));
	}
	public function get($name, $default = false) 
	{
		$value = $this->handler->get($this->getCacheKey($name));
		if( is_null($value) || false === $value ) 
		{
			return $default;
		}
		try 
		{
			$result = (0 === strpos($value, "think_serialize:") ? unserialize(substr($value, 16)) : $value);
		}
		catch( \Exception $e ) 
		{
			$result = $default;
		}
		return $result;
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
		$value = (is_scalar($value) ? $value : "think_serialize:" . serialize($value));
		if( $expire ) 
		{
			$result = $this->handler->setex($key, $expire, $value);
		}
		else 
		{
			$result = $this->handler->set($key, $value);
		}
		isset($first) and $this->setTagItem($key);
		return $result;
	}
	public function inc($name, $step = 1) 
	{
		$key = $this->getCacheKey($name);
		return $this->handler->incrby($key, $step);
	}
	public function dec($name, $step = 1) 
	{
		$key = $this->getCacheKey($name);
		return $this->handler->decrby($key, $step);
	}
	public function rm($name) 
	{
		return $this->handler->delete($this->getCacheKey($name));
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
			return $this->handler->flushDB();
		}
	}
}
?>