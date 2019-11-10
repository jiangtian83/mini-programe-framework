<?php  namespace Qiniu\Cdn;
final class CdnManager 
{
	private $auth = NULL;
	private $server = NULL;
	public function __construct(\Qiniu\Auth $auth) 
	{
		$this->auth = $auth;
		$this->server = "http://fusion.qiniuapi.com";
	}
	public function refreshUrls(array $urls) 
	{
		return $this->refreshUrlsAndDirs($urls, array( ));
	}
	public function refreshDirs(array $dirs) 
	{
		return $this->refreshUrlsAndDirs(array( ), $dirs);
	}
	public function refreshUrlsAndDirs(array $urls, array $dirs) 
	{
		$req = array( );
		if( !empty($urls) ) 
		{
			$req["urls"] = $urls;
		}
		if( !empty($dirs) ) 
		{
			$req["dirs"] = $dirs;
		}
		$url = $this->server . "/v2/tune/refresh";
		$body = json_encode($req);
		return $this->post($url, $body);
	}
	public function prefetchUrls(array $urls) 
	{
		$req = array( "urls" => $urls );
		$url = $this->server . "/v2/tune/prefetch";
		$body = json_encode($req);
		return $this->post($url, $body);
	}
	public function getBandwidthData(array $domains, $startDate, $endDate, $granularity) 
	{
		$req = array( );
		$req["domains"] = implode(";", $domains);
		$req["startDate"] = $startDate;
		$req["endDate"] = $endDate;
		$req["granularity"] = $granularity;
		$url = $this->server . "/v2/tune/bandwidth";
		$body = json_encode($req);
		return $this->post($url, $body);
	}
	public function getFluxData(array $domains, $startDate, $endDate, $granularity) 
	{
		$req = array( );
		$req["domains"] = implode(";", $domains);
		$req["startDate"] = $startDate;
		$req["endDate"] = $endDate;
		$req["granularity"] = $granularity;
		$url = $this->server . "/v2/tune/flux";
		$body = json_encode($req);
		return $this->post($url, $body);
	}
	public function getCdnLogList(array $domains, $logDate) 
	{
		$req = array( );
		$req["domains"] = implode(";", $domains);
		$req["day"] = $logDate;
		$url = $this->server . "/v2/tune/log/list";
		$body = json_encode($req);
		return $this->post($url, $body);
	}
	private function post($url, $body) 
	{
		$headers = $this->auth->authorization($url, $body, "application/json");
		$headers["Content-Type"] = "application/json";
		$ret = \Qiniu\Http\Client::post($url, $body, $headers);
		if( !$ret->ok() ) 
		{
			return array( null, new \Qiniu\Http\Error($url, $ret) );
		}
		$r = ($ret->body === null ? array( ) : $ret->json());
		return array( $r, null );
	}
	public static function createTimestampAntiLeechUrl($rawUrl, $encryptKey, $durationInSeconds) 
	{
		$parsedUrl = parse_url($rawUrl);
		$deadline = time() + $durationInSeconds;
		$expireHex = dechex($deadline);
		$path = (isset($parsedUrl["path"]) ? $parsedUrl["path"] : "");
		$path = implode("/", array_map("rawurlencode", explode("/", $path)));
		$strToSign = $encryptKey . $path . $expireHex;
		$signStr = md5($strToSign);
		if( isset($parsedUrl["query"]) ) 
		{
			$signedUrl = $rawUrl . "&sign=" . $signStr . "&t=" . $expireHex;
		}
		else 
		{
			$signedUrl = $rawUrl . "?sign=" . $signStr . "&t=" . $expireHex;
		}
		return $signedUrl;
	}
}
?>