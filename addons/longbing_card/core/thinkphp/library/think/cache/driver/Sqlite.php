<?php  namespace think\cache\driver;
class Sqlite extends \think\cache\Driver 
{
	protected $options = array( "db" => ":memory:", "table" => "sharedmemory", "prefix" => "", "expire" => 0, "persistent" => false );
	public function __construct($options = array( )) 
	{
		if( !extension_loaded("sqlite") ) 
		{
			throw new \BadFunctionCallException("not support: sqlite");
		}
		if( !empty($options) ) 
		{
			$this->options = array_merge($this->options, $options);
		}
		$func = ($this->options["persistent"] ? "sqlite_popen" : "sqlite_open");
		$this->handler = $func($this->options["db"]);
	}
	protected function getCacheKey($name) 
	{
		return $this->options["prefix"] . sqlite_escape_string($name);
	}
	public function has($name) 
	{
		$name = $this->getCacheKey($name);
		$sql = "SELECT value FROM " . $this->options["table"] . " WHERE var='" . $name . "' AND (expire=0 OR expire >" . $_SERVER["REQUEST_TIME"] . ") LIMIT 1";
		$result = sqlite_query($this->handler, $sql);
		return sqlite_num_rows($result);
	}
	public function get($name, $default = false) 
	{
		$name = $this->getCacheKey($name);
		$sql = "SELECT value FROM " . $this->options["table"] . " WHERE var='" . $name . "' AND (expire=0 OR expire >" . $_SERVER["REQUEST_TIME"] . ") LIMIT 1";
		$result = sqlite_query($this->handler, $sql);
		if( sqlite_num_rows($result) ) 
		{
			$content = sqlite_fetch_single($result);
			if( function_exists("gzcompress") ) 
			{
				$content = gzuncompress($content);
			}
			return unserialize($content);
		}
		return $default;
	}
	public function set($name, $value, $expire = NULL) 
	{
		$name = $this->getCacheKey($name);
		$value = sqlite_escape_string(serialize($value));
		if( is_null($expire) ) 
		{
			$expire = $this->options["expire"];
		}
		if( $expire instanceof \DateTime ) 
		{
			$expire = $expire->getTimestamp();
		}
		else 
		{
			$expire = (0 == $expire ? 0 : time() + $expire);
		}
		if( function_exists("gzcompress") ) 
		{
			$value = gzcompress($value, 3);
		}
		if( $this->tag ) 
		{
			$tag = $this->tag;
			$this->tag = null;
		}
		else 
		{
			$tag = "";
		}
		$sql = "REPLACE INTO " . $this->options["table"] . " (var, value, expire, tag) VALUES ('" . $name . "', '" . $value . "', '" . $expire . "', '" . $tag . "')";
		if( sqlite_query($this->handler, $sql) ) 
		{
			return true;
		}
		return false;
	}
	public function inc($name, $step = 1) 
	{
		if( $this->has($name) ) 
		{
			$value = $this->get($name) + $step;
		}
		else 
		{
			$value = $step;
		}
		return ($this->set($name, $value, 0) ? $value : false);
	}
	public function dec($name, $step = 1) 
	{
		if( $this->has($name) ) 
		{
			$value = $this->get($name) - $step;
		}
		else 
		{
			$value = 0 - $step;
		}
		return ($this->set($name, $value, 0) ? $value : false);
	}
	public function rm($name) 
	{
		$name = $this->getCacheKey($name);
		$sql = "DELETE FROM " . $this->options["table"] . " WHERE var='" . $name . "'";
		sqlite_query($this->handler, $sql);
		return true;
	}
	public function clear($tag = NULL) 
	{
		if( $tag ) 
		{
			$name = sqlite_escape_string($tag);
			$sql = "DELETE FROM " . $this->options["table"] . " WHERE tag='" . $name . "'";
			sqlite_query($this->handler, $sql);
			return true;
		}
		$sql = "DELETE FROM " . $this->options["table"];
		sqlite_query($this->handler, $sql);
		return true;
	}
}
?>