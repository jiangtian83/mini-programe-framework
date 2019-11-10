<?php  namespace Guzzle\Stream;
class PhpStreamRequestFactory implements StreamRequestFactoryInterface 
{
	protected $context = NULL;
	protected $contextOptions = NULL;
	protected $url = NULL;
	protected $lastResponseHeaders = NULL;
	public function fromRequest(\Guzzle\Http\Message\RequestInterface $request, $context = array( ), array $params = array( )) 
	{
		if( is_resource($context) ) 
		{
			$this->contextOptions = stream_context_get_options($context);
			$this->context = $context;
		}
		else 
		{
			if( is_array($context) || !$context ) 
			{
				$this->contextOptions = $context;
				$this->createContext($params);
			}
			else 
			{
				if( $context ) 
				{
					throw new \Guzzle\Common\Exception\InvalidArgumentException("\$context must be an array or resource");
				}
			}
		}
		$request->dispatch("request.before_send", array( "request" => $request, "context" => $this->context, "context_options" => $this->contextOptions ));
		$this->setUrl($request);
		$this->addDefaultContextOptions($request);
		$this->addSslOptions($request);
		$this->addBodyOptions($request);
		$this->addProxyOptions($request);
		return $this->createStream($params)->setCustomData("request", $request)->setCustomData("response_headers", $this->getLastResponseHeaders());
	}
	protected function setContextValue($wrapper, $name, $value, $overwrite = false) 
	{
		if( !isset($this->contextOptions[$wrapper]) ) 
		{
			$this->contextOptions[$wrapper] = array( $name => $value );
		}
		else 
		{
			if( !$overwrite && isset($this->contextOptions[$wrapper][$name]) ) 
			{
				return NULL;
			}
		}
		$this->contextOptions[$wrapper][$name] = $value;
		stream_context_set_option($this->context, $wrapper, $name, $value);
	}
	protected function createContext(array $params) 
	{
		$options = $this->contextOptions;
		$this->context = $this->createResource(function() use ($params, $options) 
		{
			return stream_context_create($options, $params);
		}
		);
	}
	public function getLastResponseHeaders() 
	{
		return $this->lastResponseHeaders;
	}
	protected function addDefaultContextOptions(\Guzzle\Http\Message\RequestInterface $request) 
	{
		$this->setContextValue("http", "method", $request->getMethod());
		$headers = $request->getHeaderLines();
		if( !$request->hasHeader("Connection") ) 
		{
			$headers[] = "Connection: close";
		}
		$this->setContextValue("http", "header", $headers);
		$this->setContextValue("http", "protocol_version", $request->getProtocolVersion());
		$this->setContextValue("http", "ignore_errors", true);
	}
	protected function setUrl(\Guzzle\Http\Message\RequestInterface $request) 
	{
		$this->url = $request->getUrl(true);
		if( $request->getUsername() ) 
		{
			$this->url->setUsername($request->getUsername());
		}
		if( $request->getPassword() ) 
		{
			$this->url->setPassword($request->getPassword());
		}
	}
	protected function addSslOptions(\Guzzle\Http\Message\RequestInterface $request) 
	{
		if( $request->getCurlOptions()->get(CURLOPT_SSL_VERIFYPEER) ) 
		{
			$this->setContextValue("ssl", "verify_peer", true, true);
			if( $cafile = $request->getCurlOptions()->get(CURLOPT_CAINFO) ) 
			{
				$this->setContextValue("ssl", "cafile", $cafile, true);
			}
		}
		else 
		{
			$this->setContextValue("ssl", "verify_peer", false, true);
		}
	}
	protected function addBodyOptions(\Guzzle\Http\Message\RequestInterface $request) 
	{
		if( !$request instanceof \Guzzle\Http\Message\EntityEnclosingRequestInterface ) 
		{
			return NULL;
		}
		if( count($request->getPostFields()) ) 
		{
			$this->setContextValue("http", "content", (string) $request->getPostFields(), true);
		}
		else 
		{
			if( $request->getBody() ) 
			{
				$this->setContextValue("http", "content", (string) $request->getBody(), true);
			}
		}
		if( isset($this->contextOptions["http"]["content"]) ) 
		{
			$headers = (isset($this->contextOptions["http"]["header"]) ? $this->contextOptions["http"]["header"] : array( ));
			$headers[] = "Content-Length: " . strlen($this->contextOptions["http"]["content"]);
			$this->setContextValue("http", "header", $headers, true);
		}
	}
	protected function addProxyOptions(\Guzzle\Http\Message\RequestInterface $request) 
	{
		if( $proxy = $request->getCurlOptions()->get(CURLOPT_PROXY) ) 
		{
			$this->setContextValue("http", "proxy", $proxy);
		}
	}
	protected function createStream(array $params) 
	{
		$http_response_header = null;
		$url = $this->url;
		$context = $this->context;
		$fp = $this->createResource(function() use ($context, $url, &$http_response_header) 
		{
			return fopen((string) $url, "r", false, $context);
		}
		);
		$className = (isset($params["stream_class"]) ? $params["stream_class"] : "Guzzle\\Stream" . "\\Stream");
		$stream = new $className($fp);
		if( isset($http_response_header) ) 
		{
			$this->lastResponseHeaders = $http_response_header;
			$this->processResponseHeaders($stream);
		}
		return $stream;
	}
	protected function processResponseHeaders(StreamInterface $stream) 
	{
		foreach( $this->lastResponseHeaders as $header ) 
		{
			if( stripos($header, "Content-Length:") === 0 ) 
			{
				$stream->setSize(trim(substr($header, 15)));
			}
		}
	}
	protected function createResource($callback) 
	{
		$errors = null;
		set_error_handler(function($_, $msg, $file, $line) use (&$errors) 
		{
			$errors[] = array( "message" => $msg, "file" => $file, "line" => $line );
			return true;
		}
		);
		$resource = call_user_func($callback);
		restore_error_handler();
		if( !$resource ) 
		{
			$message = "Error creating resource. ";
			foreach( $errors as $err ) 
			{
				foreach( $err as $key => $value ) 
				{
					$message .= "[" . $key . "] " . $value . PHP_EOL;
				}
			}
			throw new \Guzzle\Common\Exception\RuntimeException(trim($message));
		}
		else 
		{
			return $resource;
		}
	}
}
?>