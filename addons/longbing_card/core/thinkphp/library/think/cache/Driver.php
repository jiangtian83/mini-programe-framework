<?php  namespace think\cache;
abstract class Driver 
{
	protected $handler = NULL;
	protected $options = array( );
	protected $tag = NULL;
	abstract public function has($name);
	abstract public function get($name, $default);
	abstract public function set($name, $value, $expire);
	abstract public function inc($name, $step);
	abstract public function dec($name, $step);
	abstract public function rm($name);
	abstract public function clear($tag);
	protected function getCacheKey($name) 
	{
		return $this->options["prefix"] . $name;
	}
	public function pull($name) 
	{
		$result = $this->get($name, false);
		if( $result ) 
		{
			$this->rm($name);
			return $result;
		}
	}
	public function remember($name, $value, $expire = NULL) 
	{
		if( !$this->has($name) ) 
		{
			$time = time();
			while( time() < $time + 5 && $this->has($name . "_lock") ) 
			{
				usleep(200000);
			}
			try 
			{
				$this->set($name . "_lock", true);
				if( $value instanceof \Closure ) 
				{
					$value = call_user_func($value);
				}
				$this->set($name, $value, $expire);
				$this->rm($name . "_lock");
			}
			catch( \Exception $e ) 
			{
				$this->rm($name . "_lock");
				throw $e;
			}
			catch( \throwable $e ) 
			{
				$this->rm($name . "_lock");
				throw $e;
			}
		}
		else 
		{
			$value = $this->get($name);
		}
		return $value;
	}
	public function tag($name, $keys = NULL, $overlay = false) 
	{
		if( is_null($name) ) 
		{
		}
		else 
		{
			if( is_null($keys) ) 
			{
				$this->tag = $name;
			}
			else 
			{
				$key = "tag_" . md5($name);
				if( is_string($keys) ) 
				{
					$keys = explode(",", $keys);
				}
				$keys = array_map(array( $this, "getCacheKey" ), $keys);
				if( $overlay ) 
				{
					$value = $keys;
				}
				else 
				{
					$value = array_unique(array_merge($this->getTagItem($name), $keys));
				}
				$this->set($key, implode(",", $value), 0);
			}
		}
		return $this;
	}
	protected function setTagItem($name) 
	{
		if( $this->tag ) 
		{
			$key = "tag_" . md5($this->tag);
			$this->tag = null;
			if( $this->has($key) ) 
			{
				$value = explode(",", $this->get($key));
				$value[] = $name;
				$value = implode(",", array_unique($value));
			}
			else 
			{
				$value = $name;
			}
			$this->set($key, $value, 0);
		}
	}
	protected function getTagItem($tag) 
	{
		$key = "tag_" . md5($tag);
		$value = $this->get($key);
		if( $value ) 
		{
			return array_filter(explode(",", $value));
		}
		return array( );
	}
	public function handler() 
	{
		return $this->handler;
	}
}
?>