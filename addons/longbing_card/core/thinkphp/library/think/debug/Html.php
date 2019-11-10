<?php  namespace think\debug;
class Html 
{
	protected $config = array( "trace_file" => "", "trace_tabs" => array( "base" => "基本", "file" => "文件", "info" => "流程", "notice|error" => "错误", "sql" => "SQL", "debug|log" => "调试" ) );
	public function __construct(array $config = array( )) 
	{
		$this->config["trace_file"] = THINK_PATH . "tpl/page_trace.tpl";
		$this->config = array_merge($this->config, $config);
	}
	public function output(\think\Response $response, array $log = array( )) 
	{
		$request = \think\Request::instance();
		$contentType = $response->getHeader("Content-Type");
		$accept = $request->header("accept");
		if( strpos($accept, "application/json") === 0 || $request->isAjax() ) 
		{
			return false;
		}
		if( !empty($contentType) && strpos($contentType, "html") === false ) 
		{
			return false;
		}
		$runtime = number_format(microtime(true) - THINK_START_TIME, 10, ".", "");
		$reqs = (0 < $runtime ? number_format(1 / $runtime, 2) : "∞");
		$mem = number_format((memory_get_usage() - THINK_START_MEM) / 1024, 2);
		if( isset($_SERVER["HTTP_HOST"]) ) 
		{
			$uri = $_SERVER["SERVER_PROTOCOL"] . " " . $_SERVER["REQUEST_METHOD"] . " : " . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
		}
		else 
		{
			$uri = "cmd:" . implode(" ", $_SERVER["argv"]);
		}
		$base = array( "请求信息" => date("Y-m-d H:i:s", $_SERVER["REQUEST_TIME"]) . " " . $uri, "运行时间" => number_format($runtime, 6) . "s [ 吞吐率：" . $reqs . "req/s ] 内存消耗：" . $mem . "kb 文件加载：" . count(get_included_files()), "查询信息" => \think\Db::$queryTimes . " queries " . \think\Db::$executeTimes . " writes ", "缓存信息" => \think\Cache::$readTimes . " reads," . \think\Cache::$writeTimes . " writes", "配置加载" => count(\think\Config::get()) );
		if( session_id() ) 
		{
			$base["会话信息"] = "SESSION_ID=" . session_id();
		}
		$info = \think\Debug::getFile(true);
		$trace = array( );
		foreach( $this->config["trace_tabs"] as $name => $title ) 
		{
			$name = strtolower($name);
			switch( $name ) 
			{
				case "base": $trace[$title] = $base;
				break;
				case "file": $trace[$title] = $info;
				break;
				default: if( strpos($name, "|") ) 
				{
					$names = explode("|", $name);
					$result = array( );
					foreach( $names as $name ) 
					{
						$result = array_merge($result, (isset($log[$name]) ? $log[$name] : array( )));
					}
					$trace[$title] = $result;
				}
				else 
				{
					$trace[$title] = (isset($log[$name]) ? $log[$name] : "");
				}
			}
		}
		ob_start();
		include($this->config["trace_file"]);
		return ob_get_clean();
	}
}
?>