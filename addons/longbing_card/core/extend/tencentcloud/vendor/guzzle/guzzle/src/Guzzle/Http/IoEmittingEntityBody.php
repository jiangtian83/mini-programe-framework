<?php  namespace Guzzle\Http;
class IoEmittingEntityBody extends AbstractEntityBodyDecorator implements \Guzzle\Common\HasDispatcherInterface 
{
	protected $eventDispatcher = NULL;
	public static function getAllEvents() 
	{
		return array( "body.read", "body.write" );
	}
	public function setEventDispatcher(\Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher) 
	{
		$this->eventDispatcher = $eventDispatcher;
		return $this;
	}
	public function getEventDispatcher() 
	{
		if( !$this->eventDispatcher ) 
		{
			$this->eventDispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
		}
		return $this->eventDispatcher;
	}
	public function dispatch($eventName, array $context = array( )) 
	{
		return $this->getEventDispatcher()->dispatch($eventName, new \Guzzle\Common\Event($context));
	}
	public function addSubscriber(\Symfony\Component\EventDispatcher\EventSubscriberInterface $subscriber) 
	{
		$this->getEventDispatcher()->addSubscriber($subscriber);
		return $this;
	}
	public function read($length) 
	{
		$event = array( "body" => $this, "length" => $length, "read" => $this->body->read($length) );
		$this->dispatch("body.read", $event);
		return $event["read"];
	}
	public function write($string) 
	{
		$event = array( "body" => $this, "write" => $string, "result" => $this->body->write($string) );
		$this->dispatch("body.write", $event);
		return $event["result"];
	}
}
?>