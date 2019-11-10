<?php  namespace Qiniu\Processing;
final class Operation 
{
	private $auth = NULL;
	private $token_expire = NULL;
	private $domain = NULL;
	public function __construct($domain, $auth = NULL, $token_expire = 3600) 
	{
		$this->auth = $auth;
		$this->domain = $domain;
		$this->token_expire = $token_expire;
	}
	public function execute($key, $fops) 
	{
		$url = $this->buildUrl($key, $fops);
		$resp = \Qiniu\Http\Client::get($url);
		if( !$resp->ok() ) 
		{
			return array( null, new \Qiniu\Http\Error($url, $resp) );
		}
		if( $resp->json() !== null ) 
		{
			return array( $resp->json(), null );
		}
		return array( $resp->body, null );
	}
	public function buildUrl($key, $fops, $protocol = "http") 
	{
		if( is_array($fops) ) 
		{
			$fops = implode("|", $fops);
		}
		$url = $protocol . "://" . $this->domain . "/" . $key . "?" . $fops;
		if( $this->auth !== null ) 
		{
			$url = $this->auth->privateDownloadUrl($url, $this->token_expire);
		}
		return $url;
	}
}
?>