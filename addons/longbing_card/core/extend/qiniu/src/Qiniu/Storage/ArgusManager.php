<?php  namespace Qiniu\Storage;
final class ArgusManager 
{
	private $auth = NULL;
	private $config = NULL;
	public function __construct(\Qiniu\Auth $auth, \Qiniu\Config $config = NULL) 
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
	public function pulpVideo($body, $vid) 
	{
		$path = "/v1/video/" . $vid;
		return $this->arPost($path, $body);
	}
	private function getArHost() 
	{
		$scheme = "http://";
		if( $this->config->useHTTPS == true ) 
		{
			$scheme = "https://";
		}
		return $scheme . \Qiniu\Config::ARGUS_HOST;
	}
	private function arPost($path, $body = NULL) 
	{
		$url = $this->getArHost() . $path;
		return $this->post($url, $body);
	}
	private function post($url, $body) 
	{
		$headers = $this->auth->authorizationV2($url, "POST", $body, "application/json");
		$headers["Content-Type"] = "application/json";
		$ret = \Qiniu\Http\Client::post($url, $body, $headers);
		if( !$ret->ok() ) 
		{
			print $ret->statusCode;
			return array( null, new \Qiniu\Http\Error($url, $ret) );
		}
		$r = ($ret->body === null ? array( ) : $ret->json());
		return array( $r, null );
	}
}
?>