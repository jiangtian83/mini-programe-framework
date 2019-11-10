<?php  namespace Qiniu\Processing;
final class PersistentFop 
{
	private $auth = NULL;
	private $config = NULL;
	public function __construct($auth, $config = NULL) 
	{
		$this->auth = $auth;
		if( $config == null ) 
		{
			$this->config = new \Qiniu\Config();
		}
		else 
		{
			$this->config = $config;
		}
	}
	public function execute($bucket, $key, $fops, $pipeline = NULL, $notify_url = NULL, $force = false) 
	{
		if( is_array($fops) ) 
		{
			$fops = implode(";", $fops);
		}
		$params = array( "bucket" => $bucket, "key" => $key, "fops" => $fops );
		Qiniu\setWithoutEmpty($params, "pipeline", $pipeline);
		Qiniu\setWithoutEmpty($params, "notifyURL", $notify_url);
		if( $force ) 
		{
			$params["force"] = 1;
		}
		$data = http_build_query($params);
		$scheme = "http://";
		if( $this->config->useHTTPS === true ) 
		{
			$scheme = "https://";
		}
		$url = $scheme . \Qiniu\Config::API_HOST . "/pfop/";
		$headers = $this->auth->authorization($url, $data, "application/x-www-form-urlencoded");
		$headers["Content-Type"] = "application/x-www-form-urlencoded";
		$response = \Qiniu\Http\Client::post($url, $data, $headers);
		if( !$response->ok() ) 
		{
			return array( null, new \Qiniu\Http\Error($url, $response) );
		}
		$r = $response->json();
		$id = $r["persistentId"];
		return array( $id, null );
	}
	public function status($id) 
	{
		$scheme = "http://";
		if( $this->config->useHTTPS === true ) 
		{
			$scheme = "https://";
		}
		$url = $scheme . \Qiniu\Config::API_HOST . "/status/get/prefop?id=" . $id;
		$response = \Qiniu\Http\Client::get($url);
		if( !$response->ok() ) 
		{
			return array( null, new \Qiniu\Http\Error($url, $response) );
		}
		return array( $response->json(), null );
	}
}
?>