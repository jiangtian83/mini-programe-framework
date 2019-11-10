<?php  namespace Qiniu;
final class Auth 
{
	private $accessKey = NULL;
	private $secretKey = NULL;
	private static $policyFields = array( "callbackUrl", "callbackBody", "callbackHost", "callbackBodyType", "callbackFetchKey", "returnUrl", "returnBody", "endUser", "saveKey", "insertOnly", "detectMime", "mimeLimit", "fsizeMin", "fsizeLimit", "persistentOps", "persistentNotifyUrl", "persistentPipeline", "deleteAfterDays", "fileType", "isPrefixalScope" );
	public function __construct($accessKey, $secretKey) 
	{
		$this->accessKey = $accessKey;
		$this->secretKey = $secretKey;
	}
	public function getAccessKey() 
	{
		return $this->accessKey;
	}
	public function sign($data) 
	{
		$hmac = hash_hmac("sha1", $data, $this->secretKey, true);
		return $this->accessKey . ":" . base64_urlSafeEncode($hmac);
	}
	public function signWithData($data) 
	{
		$encodedData = base64_urlSafeEncode($data);
		return $this->sign($encodedData) . ":" . $encodedData;
	}
	public function signRequest($urlString, $body, $contentType = NULL) 
	{
		$url = parse_url($urlString);
		$data = "";
		if( array_key_exists("path", $url) ) 
		{
			$data = $url["path"];
		}
		if( array_key_exists("query", $url) ) 
		{
			$data .= "?" . $url["query"];
		}
		$data .= "\n";
		if( $body !== null && $contentType === "application/x-www-form-urlencoded" ) 
		{
			$data .= $body;
		}
		return $this->sign($data);
	}
	public function verifyCallback($contentType, $originAuthorization, $url, $body) 
	{
		$authorization = "QBox " . $this->signRequest($url, $body, $contentType);
		return $originAuthorization === $authorization;
	}
	public function privateDownloadUrl($baseUrl, $expires = 3600) 
	{
		$deadline = time() + $expires;
		$pos = strpos($baseUrl, "?");
		if( $pos !== false ) 
		{
			$baseUrl .= "&e=";
		}
		else 
		{
			$baseUrl .= "?e=";
		}
		$baseUrl .= $deadline;
		$token = $this->sign($baseUrl);
		return (string) $baseUrl . "&token=" . $token;
	}
	public function uploadToken($bucket, $key = NULL, $expires = 3600, $policy = NULL, $strictPolicy = true) 
	{
		$deadline = time() + $expires;
		$scope = $bucket;
		if( $key !== null ) 
		{
			$scope .= ":" . $key;
		}
		$args = self::copyPolicy($args, $policy, $strictPolicy);
		$args["scope"] = $scope;
		$args["deadline"] = $deadline;
		$b = json_encode($args);
		return $this->signWithData($b);
	}
	private static function copyPolicy(&$policy, $originPolicy, $strictPolicy) 
	{
		if( $originPolicy === null ) 
		{
			return array( );
		}
		foreach( $originPolicy as $key => $value ) 
		{
			if( !$strictPolicy || in_array((string) $key, self::$policyFields, true) ) 
			{
				$policy[$key] = $value;
			}
		}
		return $policy;
	}
	public function authorization($url, $body = NULL, $contentType = NULL) 
	{
		$authorization = "QBox " . $this->signRequest($url, $body, $contentType);
		return array( "Authorization" => $authorization );
	}
	public function authorizationV2($url, $method, $body = NULL, $contentType = NULL) 
	{
		$urlItems = parse_url($url);
		$host = $urlItems["host"];
		if( isset($urlItems["port"]) ) 
		{
			$port = $urlItems["port"];
		}
		else 
		{
			$port = "";
		}
		$path = $urlItems["path"];
		if( isset($urlItems["query"]) ) 
		{
			$query = $urlItems["query"];
		}
		else 
		{
			$query = "";
		}
		$toSignStr = $method . " " . $path;
		if( !empty($query) ) 
		{
			$toSignStr .= "?" . $query;
		}
		$toSignStr .= "\nHost: " . $host;
		if( !empty($port) ) 
		{
			$toSignStr .= ":" . $port;
		}
		if( !empty($contentType) ) 
		{
			$toSignStr .= "\nContent-Type: " . $contentType;
		}
		$toSignStr .= "\n\n";
		if( !empty($body) ) 
		{
			$toSignStr .= $body;
		}
		$sign = $this->sign($toSignStr);
		$auth = "Qiniu " . $sign;
		return array( "Authorization" => $auth );
	}
}
?>