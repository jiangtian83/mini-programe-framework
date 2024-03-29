<?php  namespace QcloudImage;
class HttpClient 
{
	private $proxy = "";
	private $httpInfo = NULL;
	private $curlHandler = NULL;
	public function setProxy($proxy) 
	{
		$this->proxy = $proxy;
	}
	public function __destory() 
	{
		if( $this->curlHandler ) 
		{
			curl_close($this->curlHandler);
		}
	}
	public function sendRequest($request) 
	{
		if( !is_array($request) || !isset($request["url"]) ) 
		{
			return false;
		}
		if( $this->curlHandler ) 
		{
			if( function_exists("curl_reset") ) 
			{
				curl_reset($this->curlHandler);
			}
			else 
			{
				my_curl_reset($this->curlHandler);
			}
		}
		else 
		{
			$this->curlHandler = curl_init();
		}
		curl_setopt($this->curlHandler, CURLOPT_URL, $request["url"]);
		$method = "GET";
		if( isset($request["method"]) && in_array(strtolower($request["method"]), array( "get", "post", "put", "delete", "head" )) ) 
		{
			$method = strtoupper($request["method"]);
		}
		else 
		{
			if( isset($request["data"]) ) 
			{
				$method = "POST";
			}
		}
		$header = (isset($request["header"]) ? $request["header"] : array( ));
		$header[] = "Connection: keep-alive";
		if( "POST" == $method ) 
		{
			$header[] = "Expect: ";
		}
		isset($request["host"]) and $header[] = "Host:" . $request["host"];
		curl_setopt($this->curlHandler, CURLOPT_HTTPHEADER, $header);
		if( empty($this->proxy) ) 
		{
			curl_setopt($this->curlHandler, CURLOPT_PROXY, null);
		}
		else 
		{
			curl_setopt($this->curlHandler, CURLOPT_PROXY, $this->proxy);
		}
		curl_setopt($this->curlHandler, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->curlHandler, CURLOPT_CUSTOMREQUEST, $method);
		isset($request["timeout"]) and curl_setopt($this->curlHandler, CURLOPT_TIMEOUT, $request["timeout"]);
		isset($request["data"]) and isset($request["data"]) && in_array($method, array( "POST", "PUT" )) and curl_setopt($this->curlHandler, CURLOPT_POSTFIELDS, $request["data"]);
		$ssl = (substr($request["url"], 0, 8) == "https://" ? true : false);
		if( isset($request["cert"]) ) 
		{
			curl_setopt($this->curlHandler, CURLOPT_SSL_VERIFYPEER, true);
			curl_setopt($this->curlHandler, CURLOPT_CAINFO, $request["cert"]);
			curl_setopt($this->curlHandler, CURLOPT_SSL_VERIFYHOST, 2);
			if( isset($request["ssl_version"]) ) 
			{
				curl_setopt($this->curlHandler, CURLOPT_SSLVERSION, $request["ssl_version"]);
			}
			else 
			{
				curl_setopt($this->curlHandler, CURLOPT_SSLVERSION, 4);
			}
		}
		else 
		{
			if( $ssl ) 
			{
				curl_setopt($this->curlHandler, CURLOPT_SSL_VERIFYPEER, true);
				curl_setopt($this->curlHandler, CURLOPT_SSL_VERIFYHOST, 2);
				if( isset($request["ssl_version"]) ) 
				{
					curl_setopt($this->curlHandler, CURLOPT_SSLVERSION, $request["ssl_version"]);
				}
				else 
				{
					curl_setopt($this->curlHandler, CURLOPT_SSLVERSION, 4);
				}
			}
		}
		$ret = curl_exec($this->curlHandler);
		$this->httpInfo = curl_getinfo($this->curlHandler);
		return $ret;
	}
	public function statusCode() 
	{
		if( $this->httpInfo ) 
		{
			return $this->httpInfo["http_code"];
		}
		return 0;
	}
}
function my_curl_reset($handler) 
{
	curl_setopt($handler, CURLOPT_URL, "");
	curl_setopt($handler, CURLOPT_HTTPHEADER, array( ));
	curl_setopt($handler, CURLOPT_POSTFIELDS, array( ));
	curl_setopt($handler, CURLOPT_TIMEOUT, 0);
	curl_setopt($handler, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($handler, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($handler, CURLOPT_PROXY, null);
}
?>