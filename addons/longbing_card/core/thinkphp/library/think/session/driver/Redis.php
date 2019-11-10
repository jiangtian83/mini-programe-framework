<?php  namespace think\session\driver;
class Redis extends \SessionHandler 
{
	protected $handler = NULL;
	protected $config = array( "host" => "127.0.0.1", "port" => 6379, "password" => "", "select" => 0, "expire" => 3600, "timeout" => 0, "persistent" => true, "session_name" => "" );
	public function __construct($config = array( )) 
	{
		$this->config = array_merge($this->config, $config);
	}
	public function open($savePath, $sessName) 
	{
		if( !extension_loaded("redis") ) 
		{
			throw new \think\Exception("not support:redis");
		}
		$this->handler = new \Redis();
		$func = ($this->config["persistent"] ? "pconnect" : "connect");
		$this->handler->$func($this->config["host"], $this->config["port"], $this->config["timeout"]);
		if( "" != $this->config["password"] ) 
		{
			$this->handler->auth($this->config["password"]);
		}
		if( 0 != $this->config["select"] ) 
		{
			$this->handler->select($this->config["select"]);
		}
		return true;
	}
	public function close() 
	{
		$this->gc(ini_get("session.gc_maxlifetime"));
		$this->handler->close();
		$this->handler = null;
		return true;
	}
	public function read($sessID) 
	{
		return (string) $this->handler->get($this->config["session_name"] . $sessID);
	}
	public function write($sessID, $sessData) 
	{
		if( 0 < $this->config["expire"] ) 
		{
			return $this->handler->setex($this->config["session_name"] . $sessID, $this->config["expire"], $sessData);
		}
		return $this->handler->set($this->config["session_name"] . $sessID, $sessData);
	}
	public function destroy($sessID) 
	{
		return 0 < $this->handler->delete($this->config["session_name"] . $sessID);
	}
	public function gc($sessMaxLifeTime) 
	{
		return true;
	}
}
?>