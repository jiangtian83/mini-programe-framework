<?php  namespace Guzzle\Plugin\Mock;
class MockPlugin extends \Guzzle\Common\AbstractHasDispatcher implements \Symfony\Component\EventDispatcher\EventSubscriberInterface, \Countable 
{
	protected $queue = array( );
	protected $temporary = false;
	protected $received = array( );
	protected $readBodies = NULL;
	public function __construct(array $items = NULL, $temporary = false, $readBodies = false) 
	{
		$this->readBodies = $readBodies;
		$this->temporary = $temporary;
		if( $items ) 
		{
			foreach( $items as $item ) 
			{
				if( $item instanceof \Exception ) 
				{
					$this->addException($item);
				}
				else 
				{
					$this->addResponse($item);
				}
			}
		}
	}
	public static function getSubscribedEvents() 
	{
		return array( "request.before_send" => array( "onRequestBeforeSend", -999 ) );
	}
	public static function getAllEvents() 
	{
		return array( "mock.request" );
	}
	public static function getMockFile($path) 
	{
		if( !file_exists($path) ) 
		{
			throw new \Guzzle\Common\Exception\InvalidArgumentException("Unable to open mock file: " . $path);
		}
		return \Guzzle\Http\Message\Response::fromMessage(file_get_contents($path));
	}
	public function readBodies($readBodies) 
	{
		$this->readBodies = $readBodies;
		return $this;
	}
	public function count() 
	{
		return count($this->queue);
	}
	public function addResponse($response) 
	{
		if( !$response instanceof \Guzzle\Http\Message\Response ) 
		{
			if( !is_string($response) ) 
			{
				throw new \Guzzle\Common\Exception\InvalidArgumentException("Invalid response");
			}
			$response = self::getMockFile($response);
		}
		$this->queue[] = $response;
		return $this;
	}
	public function addException(\Guzzle\Http\Exception\CurlException $e) 
	{
		$this->queue[] = $e;
		return $this;
	}
	public function clearQueue() 
	{
		$this->queue = array( );
		return $this;
	}
	public function getQueue() 
	{
		return $this->queue;
	}
	public function isTemporary() 
	{
		return $this->temporary;
	}
	public function dequeue(\Guzzle\Http\Message\RequestInterface $request) 
	{
		$this->dispatch("mock.request", array( "plugin" => $this, "request" => $request ));
		$item = array_shift($this->queue);
		if( $item instanceof \Guzzle\Http\Message\Response ) 
		{
			if( $this->readBodies && $request instanceof \Guzzle\Http\Message\EntityEnclosingRequestInterface ) 
			{
				$request->getEventDispatcher()->addListener("request.sent", $f = function(\Guzzle\Common\Event $event) use (&$f) 
				{
					while( $data = $event["request"]->getBody()->read(8096) ) 
					{
					}
					$event["request"]->getEventDispatcher()->removeListener("request.sent", $f);
				}
				);
			}
			$request->setResponse($item);
		}
		else 
		{
			if( $item instanceof \Guzzle\Http\Exception\CurlException ) 
			{
				$item->setRequest($request);
				$state = $request->setState(\Guzzle\Http\Message\RequestInterface::STATE_ERROR, array( "exception" => $item ));
				if( $state == \Guzzle\Http\Message\RequestInterface::STATE_ERROR ) 
				{
					throw $item;
				}
			}
		}
		return $this;
	}
	public function flush() 
	{
		$this->received = array( );
	}
	public function getReceivedRequests() 
	{
		return $this->received;
	}
	public function onRequestBeforeSend(\Guzzle\Common\Event $event) 
	{
		if( !$this->queue ) 
		{
			throw new \OutOfBoundsException("Mock queue is empty");
		}
		$request = $event["request"];
		$this->received[] = $request;
		if( $this->temporary && count($this->queue) == 1 && $request->getClient() ) 
		{
			$request->getClient()->getEventDispatcher()->removeSubscriber($this);
		}
		$this->dequeue($request);
	}
}
?>