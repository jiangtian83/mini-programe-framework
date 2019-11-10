<?php  namespace Guzzle\Log;
class MessageFormatter 
{
	protected $template = NULL;
	const DEFAULT_FORMAT = "{hostname} {req_header_User-Agent} - [{ts}] \"{method} {resource} {protocol}/{version}\" {code} {res_header_Content-Length}";
	const DEBUG_FORMAT = ">>>>>>>>\n{request}\n<<<<<<<<\n{response}\n--------\n{curl_stderr}";
	const SHORT_FORMAT = "[{ts}] \"{method} {resource} {protocol}/{version}\" {code}";
	public function __construct($template = self::DEFAULT_FORMAT) 
	{
		$this->template = ($template ?: self::DEFAULT_FORMAT);
	}
	public function setTemplate($template) 
	{
		$this->template = $template;
		return $this;
	}
	public function format(\Guzzle\Http\Message\RequestInterface $request, \Guzzle\Http\Message\Response $response = NULL, \Guzzle\Http\Curl\CurlHandle $handle = NULL, array $customData = array( )) 
	{
		$cache = $customData;
		return preg_replace_callback("/{\\s*([A-Za-z_\\-\\.0-9]+)\\s*}/", function(array $matches) use ($request, $response, $handle, &$cache) 
		{
			if( array_key_exists($matches[1], $cache) ) 
			{
				return $cache[$matches[1]];
			}
			$result = "";
			switch( $matches[1] ) 
			{
				case "request": $result = (string) $request;
				break;
				case "response": $result = (string) $response;
				break;
				case "req_body": $result = ($request instanceof \Guzzle\Http\Message\EntityEnclosingRequestInterface ? (string) $request->getBody() : "");
				break;
				case "res_body": $result = ($response ? $response->getBody(true) : "");
				break;
				case "ts": $result = gmdate("c");
				break;
				case "method": $result = $request->getMethod();
				break;
				case "url": $result = (string) $request->getUrl();
				break;
				case "resource": $result = $request->getResource();
				break;
				case "protocol": $result = "HTTP";
				break;
				case "version": $result = $request->getProtocolVersion();
				break;
				case "host": $result = $request->getHost();
				break;
				case "hostname": $result = gethostname();
				break;
				case "port": $result = $request->getPort();
				break;
				case "code": $result = ($response ? $response->getStatusCode() : "");
				break;
				case "phrase": $result = ($response ? $response->getReasonPhrase() : "");
				break;
				case "connect_time": $result = ($handle && $handle->getInfo(CURLINFO_CONNECT_TIME) ? $handle->getInfo(CURLINFO_CONNECT_TIME) : ($response ? $response->getInfo("connect_time") : ""));
				break;
				case "total_time": $result = ($handle && $handle->getInfo(CURLINFO_TOTAL_TIME) ? $handle->getInfo(CURLINFO_TOTAL_TIME) : ($response ? $response->getInfo("total_time") : ""));
				break;
				case "curl_error": $result = ($handle ? $handle->getError() : "");
				break;
				case "curl_code": $result = ($handle ? $handle->getErrorNo() : "");
				break;
				case "curl_stderr": $result = ($handle ? $handle->getStderr() : "");
				break;
				default: if( strpos($matches[1], "req_header_") === 0 ) 
				{
					$result = $request->getHeader(substr($matches[1], 11));
				}
				else 
				{
					if( $response && strpos($matches[1], "res_header_") === 0 ) 
					{
						$result = $response->getHeader(substr($matches[1], 11));
					}
				}
			}
			$cache[$matches[1]] = $result;
			return $result;
		}
		, $this->template);
	}
}
?>