<?php  namespace Qcloud\Cos;
class ExceptionListener implements \Symfony\Component\EventDispatcher\EventSubscriberInterface 
{
	protected $parser = NULL;
	protected $defaultException = NULL;
	public function __construct() 
	{
		$this->parser = new ExceptionParser();
		$this->defaultException = "Qcloud\\Cos\\Exception\\ServiceResponseException";
	}
	public static function getSubscribedEvents() 
	{
		return array( "request.error" => array( "onRequestError", -1 ) );
	}
	public function onRequestError(\Guzzle\Common\Event $event) 
	{
		$e = $this->fromResponse($event["request"], $event["response"]);
		$event->stopPropagation();
		throw $e;
	}
	public function fromResponse(\Guzzle\Http\Message\RequestInterface $request, \Guzzle\Http\Message\Response $response) 
	{
		$parts = $this->parser->parse($request, $response);
		$className = "Qcloud\\Cos\\Exception\\" . $parts["code"];
		if( substr($className, -9) !== "Exception" ) 
		{
			$className .= "Exception";
		}
		$className = (class_exists($className) ? $className : $this->defaultException);
		return $this->createException($className, $request, $response, $parts);
	}
	protected function createException($className, \Guzzle\Http\Message\RequestInterface $request, \Guzzle\Http\Message\Response $response, array $parts) 
	{
		$class = new $className($parts["message"]);
		if( $class instanceof Exception\ServiceResponseException ) 
		{
			$class->setExceptionCode($parts["code"]);
			$class->setExceptionType($parts["type"]);
			$class->setResponse($response);
			$class->setRequest($request);
			$class->setRequestId($parts["request_id"]);
		}
		return $class;
	}
}
?>