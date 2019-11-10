<?php  namespace think\log\driver;
class Socket 
{
	public $port = 1116;
	protected $config = array( "host" => "localhost", "show_included_files" => false, "force_client_ids" => array( ), "allow_client_ids" => array( ) );
	protected $css = array( "sql" => "color:#009bb4;", "sql_warn" => "color:#009bb4;font-size:14px;", "error" => "color:#f4006b;font-size:14px;", "page" => "color:#40e2ff;background:#171717;", "big" => "font-size:20px;color:red;" );
	protected $allowForceClientIds = array( );
	public function __construct(array $config = array( )) 
	{
		if( !empty($config) ) 
		{
			$this->config = array_merge($this->config, $config);
		}
	}
	public function save(array $log = array( )) 
	{
		if( !$this->check() ) 
		{
			return false;
		}
		$trace = array( );
		if( \think\App::$debug ) 
		{
			$runtime = round(microtime(true) - THINK_START_TIME, 10);
			$reqs = (0 < $runtime ? number_format(1 / $runtime, 2) : "∞");
			$time_str = " [运行时间：" . number_format($runtime, 6) . "s][吞吐率：" . $reqs . "req/s]";
			$memory_use = number_format((memory_get_usage() - THINK_START_MEM) / 1024, 2);
			$memory_str = " [内存消耗：" . $memory_use . "kb]";
			$file_load = " [文件加载：" . count(get_included_files()) . "]";
			if( isset($_SERVER["HTTP_HOST"]) ) 
			{
				$current_uri = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
			}
			else 
			{
				$current_uri = "cmd:" . implode(" ", $_SERVER["argv"]);
			}
			$trace[] = array( "type" => "group", "msg" => $current_uri . $time_str . $memory_str . $file_load, "css" => $this->css["page"] );
		}
		foreach( $log as $type => $val ) 
		{
			$trace[] = array( "type" => "groupCollapsed", "msg" => "[ " . $type . " ]", "css" => (isset($this->css[$type]) ? $this->css[$type] : "") );
			foreach( $val as $msg ) 
			{
				if( !is_string($msg) ) 
				{
					$msg = var_export($msg, true);
				}
				$trace[] = array( "type" => "log", "msg" => $msg, "css" => "" );
			}
			$trace[] = array( "type" => "groupEnd", "msg" => "", "css" => "" );
		}
		if( $this->config["show_included_files"] ) 
		{
			$trace[] = array( "type" => "groupCollapsed", "msg" => "[ file ]", "css" => "" );
			$trace[] = array( "type" => "log", "msg" => implode("\n", get_included_files()), "css" => "" );
			$trace[] = array( "type" => "groupEnd", "msg" => "", "css" => "" );
		}
		$trace[] = array( "type" => "groupEnd", "msg" => "", "css" => "" );
		$tabid = $this->getClientArg("tabid");
		if( !($client_id = $this->getClientArg("client_id")) ) 
		{
			$client_id = "";
		}
		if( !empty($this->allowForceClientIds) ) 
		{
			foreach( $this->allowForceClientIds as $force_client_id ) 
			{
				$client_id = $force_client_id;
				$this->sendToClient($tabid, $client_id, $trace, $force_client_id);
			}
		}
		else 
		{
			$this->sendToClient($tabid, $client_id, $trace, "");
		}
		return true;
	}
	protected function sendToClient($tabid, $client_id, $logs, $force_client_id) 
	{
		$logs = array( "tabid" => $tabid, "client_id" => $client_id, "logs" => $logs, "force_client_id" => $force_client_id );
		$msg = @json_encode($logs);
		$address = "/" . $client_id;
		$this->send($this->config["host"], $msg, $address);
	}
	protected function check() 
	{
		$tabid = $this->getClientArg("tabid");
		if( !$tabid && !$this->config["force_client_ids"] ) 
		{
			return false;
		}
		$allow_client_ids = $this->config["allow_client_ids"];
		if( !empty($allow_client_ids) ) 
		{
			$this->allowForceClientIds = array_intersect($allow_client_ids, $this->config["force_client_ids"]);
			if( !$tabid && count($this->allowForceClientIds) ) 
			{
				return true;
			}
			$client_id = $this->getClientArg("client_id");
			if( !in_array($client_id, $allow_client_ids) ) 
			{
				return false;
			}
		}
		else 
		{
			$this->allowForceClientIds = $this->config["force_client_ids"];
		}
		return true;
	}
	protected function getClientArg($name) 
	{
		static $args = array( );
		$key = "HTTP_USER_AGENT";
		if( isset($_SERVER["HTTP_SOCKETLOG"]) ) 
		{
			$key = "HTTP_SOCKETLOG";
		}
		if( !isset($_SERVER[$key]) ) 
		{
			return NULL;
		}
		if( empty($args) ) 
		{
			if( !preg_match("/SocketLog\\((.*?)\\)/", $_SERVER[$key], $match) ) 
			{
				$args = array( "tabid" => null );
				return NULL;
			}
			parse_str($match[1], $args);
		}
		if( isset($args[$name]) ) 
		{
			return $args[$name];
		}
	}
	protected function send($host, $message = "", $address = "/") 
	{
		$url = "http://" . $host . ":" . $this->port . $address;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $message);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$headers = array( "Content-Type: application/json;charset=UTF-8" );
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		return curl_exec($ch);
	}
}
?>