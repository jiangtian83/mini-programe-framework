<?php  namespace Qcloud\Cos;
class ExceptionParser 
{
	public function parse(\Guzzle\Http\Message\RequestInterface $request, \Guzzle\Http\Message\Response $response) 
	{
		$data = array( "code" => null, "message" => null, "type" => ($response->isClientError() ? "client" : "server"), "request_id" => null, "parsed" => null );
		$body = $response->getBody(true);
		if( !$body ) 
		{
			$this->parseHeaders($request, $response, $data);
			return $data;
		}
		try 
		{
			$xml = new \SimpleXMLElement($body);
			$this->parseBody($xml, $data);
			return $data;
		}
		catch( \Exception $e ) 
		{
			$data["code"] = "PhpInternalXmlParseError";
			$data["message"] = "A non-XML response was received";
			return $data;
		}
	}
	protected function parseHeaders(\Guzzle\Http\Message\RequestInterface $request, \Guzzle\Http\Message\Response $response, array &$data) 
	{
		$data["message"] = $response->getStatusCode() . " " . $response->getReasonPhrase();
		if( $requestId = $response->getHeader("x-cos-request-id") ) 
		{
			$data["request_id"] = $requestId;
			$data["message"] .= " (Request-ID: " . $requestId . ")";
		}
		$status = $response->getStatusCode();
		$method = $request->getMethod();
		if( $status === 403 ) 
		{
			$data["code"] = "AccessDenied";
		}
		else 
		{
			if( $method === "HEAD" && $status === 404 ) 
			{
				$path = explode("/", trim($request->getPath(), "/"));
				$host = explode(".", $request->getHost());
				$bucket = (4 <= count($host) ? $host[0] : array_shift($path));
				$object = array_shift($path);
				if( $bucket && $object ) 
				{
					$data["code"] = "NoSuchKey";
				}
				else 
				{
					if( $bucket ) 
					{
						$data["code"] = "NoSuchBucket";
					}
				}
			}
		}
	}
	protected function parseBody(\SimpleXMLElement $body, array &$data) 
	{
		$data["parsed"] = $body;
		$namespaces = $body->getDocNamespaces();
		if( isset($namespaces[""]) ) 
		{
			$body->registerXPathNamespace("ns", $namespaces[""]);
			$prefix = "ns:";
		}
		else 
		{
			$prefix = "";
		}
		if( $tempXml = $body->xpath("//" . $prefix . "Code[1]") ) 
		{
			$data["code"] = (string) $tempXml[0];
		}
		if( $tempXml = $body->xpath("//" . $prefix . "Message[1]") ) 
		{
			$data["message"] = (string) $tempXml[0];
		}
		$tempXml = $body->xpath("//" . $prefix . "RequestId[1]");
		if( empty($tempXml) ) 
		{
			$tempXml = $body->xpath("//" . $prefix . "RequestID[1]");
		}
		if( isset($tempXml[0]) ) 
		{
			$data["request_id"] = (string) $tempXml[0];
		}
	}
}
?>