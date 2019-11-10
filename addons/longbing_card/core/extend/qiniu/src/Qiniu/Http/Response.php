<?php  namespace Qiniu\Http;
final class Response 
{
	public $statusCode = NULL;
	public $headers = NULL;
	public $body = NULL;
	public $error = NULL;
	private $jsonData = NULL;
	public $duration = NULL;
	private static $statusTexts = array( "100" => "Continue", "101" => "Switching Protocols", "102" => "Processing", "200" => "OK", "201" => "Created", "202" => "Accepted", "203" => "Non-Authoritative Information", "204" => "No Content", "205" => "Reset Content", "206" => "Partial Content", "207" => "Multi-Status", "208" => "Already Reported", "226" => "IM Used", "300" => "Multiple Choices", "301" => "Moved Permanently", "302" => "Found", "303" => "See Other", "304" => "Not Modified", "305" => "Use Proxy", "307" => "Temporary Redirect", "308" => "Permanent Redirect", "400" => "Bad Request", "401" => "Unauthorized", "402" => "Payment Required", "403" => "Forbidden", "404" => "Not Found", "405" => "Method Not Allowed", "406" => "Not Acceptable", "407" => "Proxy Authentication Required", "408" => "Request Timeout", "409" => "Conflict", "410" => "Gone", "411" => "Length Required", "412" => "Precondition Failed", "413" => "Request Entity Too Large", "414" => "Request-URI Too Long", "415" => "Unsupported Media Type", "416" => "Requested Range Not Satisfiable", "417" => "Expectation Failed", "422" => "Unprocessable Entity", "423" => "Locked", "424" => "Failed Dependency", "425" => "Reserved for WebDAV advanced collections expired proposal", "426" => "Upgrade required", "428" => "Precondition Required", "429" => "Too Many Requests", "431" => "Request Header Fields Too Large", "500" => "Internal Server Error", "501" => "Not Implemented", "502" => "Bad Gateway", "503" => "Service Unavailable", "504" => "Gateway Timeout", "505" => "HTTP Version Not Supported", "506" => "Variant Also Negotiates (Experimental)", "507" => "Insufficient Storage", "508" => "Loop Detected", "510" => "Not Extended", "511" => "Network Authentication Required" );
	public function __construct($code, $duration, array $headers = array( ), $body = NULL, $error = NULL) 
	{
		$this->statusCode = $code;
		$this->duration = $duration;
		$this->headers = $headers;
		$this->body = $body;
		$this->error = $error;
		$this->jsonData = null;
		if( $error !== null ) 
		{
			return NULL;
		}
		if( $body === null ) 
		{
			if( 400 <= $code ) 
			{
				$this->error = self::$statusTexts[$code];
			}
		}
		else 
		{
			if( self::isJson($headers) ) 
			{
				try 
				{
					$jsonData = self::bodyJson($body);
					if( 400 <= $code ) 
					{
						$this->error = $body;
						if( $jsonData["error"] !== null ) 
						{
							$this->error = $jsonData["error"];
						}
					}
					$this->jsonData = $jsonData;
				}
				catch( \InvalidArgumentException $e ) 
				{
					$this->error = $body;
					if( 200 <= $code && $code < 300 ) 
					{
						$this->error = $e->getMessage();
					}
				}
			}
			else 
			{
				if( 400 <= $code ) 
				{
					$this->error = $body;
				}
			}
		}
	}
	public function json() 
	{
		return $this->jsonData;
	}
	private static function bodyJson($body) 
	{
		return Qiniu\json_decode((string) $body, true, 512);
	}
	public function xVia() 
	{
		$via = $this->headers["X-Via"];
		if( $via === null ) 
		{
			$via = $this->headers["X-Px"];
		}
		if( $via === null ) 
		{
			$via = $this->headers["Fw-Via"];
		}
		return $via;
	}
	public function xLog() 
	{
		return $this->headers["X-Log"];
	}
	public function xReqId() 
	{
		return $this->headers["X-Reqid"];
	}
	public function ok() 
	{
		return 200 <= $this->statusCode && $this->statusCode < 300 && $this->error === null;
	}
	public function needRetry() 
	{
		$code = $this->statusCode;
		if( $code < 0 || $code / 100 === 5 && $code !== 579 || $code === 996 ) 
		{
			return true;
		}
	}
	private static function isJson($headers) 
	{
		return array_key_exists("Content-Type", $headers) && strpos($headers["Content-Type"], "application/json") === 0;
	}
}
?>