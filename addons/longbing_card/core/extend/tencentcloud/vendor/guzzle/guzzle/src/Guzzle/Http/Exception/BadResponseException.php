<?php  namespace Guzzle\Http\Exception;
class BadResponseException extends RequestException 
{
	private $response = NULL;
	public static function factory(\Guzzle\Http\Message\RequestInterface $request, \Guzzle\Http\Message\Response $response) 
	{
		if( $response->isClientError() ) 
		{
			$label = "Client error response";
			$class = "Guzzle\\Http\\Exception" . "\\ClientErrorResponseException";
		}
		else 
		{
			if( $response->isServerError() ) 
			{
				$label = "Server error response";
				$class = "Guzzle\\Http\\Exception" . "\\ServerErrorResponseException";
			}
			else 
			{
				$label = "Unsuccessful response";
				$class = "Guzzle\\Http\\Exception\\BadResponseException";
			}
		}
		$message = $label . PHP_EOL . implode(PHP_EOL, array( "[status code] " . $response->getStatusCode(), "[reason phrase] " . $response->getReasonPhrase(), "[url] " . $request->getUrl() ));
		$e = new $class($message);
		$e->setResponse($response);
		$e->setRequest($request);
		return $e;
	}
	public function setResponse(\Guzzle\Http\Message\Response $response) 
	{
		$this->response = $response;
	}
	public function getResponse() 
	{
		return $this->response;
	}
}
?>