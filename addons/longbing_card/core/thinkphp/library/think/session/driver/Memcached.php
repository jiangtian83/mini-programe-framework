<?php  namespace think\session\driver;
class Memcached extends \SessionHandler 
{
	protected $handler = NULL;
	protected $config = array( "host" => "127.0.0.1", "port" => 11211, "expire" => 3600, "timeout" => 0, "session_name" => "", "username" => "", "password" => "" );
	public function __construct($config = array( )) 
	{
		$this->config = array_merge($this->config, $config);
	}
	public function open($savePath, $sessName) 
	{
		if( !extension_loaded("memcached") ) 
		{
			throw new \think\Exception("not support:memcached");
		}
		$this->handler = new \Memcached();
		if( 0 < $this->config["timeout"] ) 
		{
			$this->handler->setOption(\Memcached::OPT_CONNECT_TIMEOUT, $this->config["timeout"]);
		}
		$hosts = explode(",", $this->config["host"]);
		$ports = explode(",", $this->config["port"]);
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
		if( "" != $this->config["username"] ) 
		{
			$this->handler->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);
			$this->handler->setSaslAuthData($this->config["username"], $this->config["password"]);
		}
		return true;
	}
	public function close() 
	{
		$this->gc(ini_get("session.gc_maxlifetime"));
		$this->handler->quit();
		$this->handler = null;
		return true;
	}
	public function read($sessID) 
	{
		return (string) $this->handler->get($this->config["session_name"] . $sessID);
	}
	public function write($sessID, $sessData) 
	{
		return $this->handler->set($this->config["session_name"] . $sessID, $sessData, $this->config["expire"]);
	}
	public function destroy($sessID) 
	{
		return $this->handler->delete($this->config["session_name"] . $sessID);
	}
	public function gc($sessMaxLifeTime) 
	{
		return true;
	}
}
?>