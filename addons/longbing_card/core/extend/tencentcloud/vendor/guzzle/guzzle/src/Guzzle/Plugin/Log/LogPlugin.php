<?php  namespace Guzzle\Plugin\Log;
class LogPlugin implements \Symfony\Component\EventDispatcher\EventSubscriberInterface 
{
	protected $logAdapter = NULL;
	protected $formatter = NULL;
	protected $wireBodies = NULL;
	public function __construct(\Guzzle\Log\LogAdapterInterface $logAdapter, $formatter = NULL, $wireBodies = false) 
	{
		$this->logAdapter = $logAdapter;
		$this->formatter = ($formatter instanceof \Guzzle\Log\MessageFormatter ? $formatter : new \Guzzle\Log\MessageFormatter($formatter));
		$this->wireBodies = $wireBodies;
	}
	public static function getDebugPlugin($wireBodies = true, $stream = NULL) 
	{
		if( $stream === null ) 
		{
			if( defined("STDERR") ) 
			{
				$stream = STDERR;
			}
			else 
			{
				$stream = fopen("php://output", "w");
			}
		}
		return new self(new \Guzzle\Log\ClosureLogAdapter(function($m) use ($stream) 
		{
			fwrite($stream, $m . PHP_EOL);
		}
		), "# Request:\n{request}\n\n# Response:\n{response}\n\n# Errors: {curl_code} {curl_error}", $wireBodies);
	}
	public static function getSubscribedEvents() 
	{
		return array( "curl.callback.write" => array( "onCurlWrite", 255 ), "curl.callback.read" => array( "onCurlRead", 255 ), "request.before_send" => array( "onRequestBeforeSend", 255 ), "request.sent" => array( "onRequestSent", 255 ) );
	}
	public function onCurlRead(\Guzzle\Common\Event $event) 
	{
		if( $wire = $event["request"]->getParams()->get("request_wire") ) 
		{
			$wire->write($event["read"]);
		}
	}
	public function onCurlWrite(\Guzzle\Common\Event $event) 
	{
		if( $wire = $event["request"]->getParams()->get("response_wire") ) 
		{
			$wire->write($event["write"]);
		}
	}
	public function onRequestBeforeSend(\Guzzle\Common\Event $event) 
	{
		if( $this->wireBodies ) 
		{
			$request = $event["request"];
			$request->getCurlOptions()->set("emit_io", true);
			if( $request instanceof \Guzzle\Http\Message\EntityEnclosingRequestInterface && $request->getBody() && (!$request->getBody()->isSeekable() || !$request->getBody()->isReadable()) ) 
			{
				$request->getParams()->set("request_wire", \Guzzle\Http\EntityBody::factory());
			}
			if( !$request->getResponseBody()->isRepeatable() ) 
			{
				$request->getParams()->set("response_wire", \Guzzle\Http\EntityBody::factory());
			}
		}
	}
	public function onRequestSent(\Guzzle\Common\Event $event) 
	{
		$request = $event["request"];
		$response = $event["response"];
		$handle = $event["handle"];
		if( $wire = $request->getParams()->get("request_wire") ) 
		{
			$request = clone $request;
			$request->setBody($wire);
		}
		if( $wire = $request->getParams()->get("response_wire") ) 
		{
			$response = clone $response;
			$response->setBody($wire);
		}
		$priority = ($response && $response->isError() ? LOG_ERR : LOG_DEBUG);
		$message = $this->formatter->format($request, $response, $handle);
		$this->logAdapter->log($message, $priority, array( "request" => $request, "response" => $response, "handle" => $handle ));
	}
}
?>