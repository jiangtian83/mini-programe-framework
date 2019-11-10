<?php  namespace Guzzle\Plugin\ErrorResponse;
class ErrorResponsePlugin implements \Symfony\Component\EventDispatcher\EventSubscriberInterface 
{
	public static function getSubscribedEvents() 
	{
		return array( "command.before_send" => array( "onCommandBeforeSend", -1 ) );
	}
	public function onCommandBeforeSend(\Guzzle\Common\Event $event) 
	{
		$command = $event["command"];
		if( ($operation = $command->getOperation()) && $operation->getErrorResponses() ) 
		{
			$request = $command->getRequest();
			$request->getEventDispatcher()->addListener("request.complete", $this->getErrorClosure($request, $command, $operation));
		}
	}
	protected function getErrorClosure(\Guzzle\Http\Message\RequestInterface $request, \Guzzle\Service\Command\CommandInterface $command, \Guzzle\Service\Description\Operation $operation) 
	{
		return function(\Guzzle\Common\Event $event) use ($request, $command, $operation) 
		{
			$response = $event["response"];
			foreach( $operation->getErrorResponses() as $error ) 
			{
				if( !isset($error["class"]) ) 
				{
					continue;
				}
				if( isset($error["code"]) && $response->getStatusCode() != $error["code"] ) 
				{
					continue;
				}
				if( isset($error["reason"]) && $response->getReasonPhrase() != $error["reason"] ) 
				{
					continue;
				}
				$className = $error["class"];
				$errorClassInterface = "Guzzle\\Plugin\\ErrorResponse" . "\\ErrorResponseExceptionInterface";
				if( !class_exists($className) ) 
				{
					throw new Exception\ErrorResponseException((string) $className . " does not exist");
				}
				if( !in_array($errorClassInterface, class_implements($className)) ) 
				{
					throw new Exception\ErrorResponseException((string) $className . " must implement " . $errorClassInterface);
				}
				throw $className::fromCommand($command, $response);
			}
		}
		;
	}
}
?>