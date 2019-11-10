<?php  namespace Guzzle\Common;
class AbstractHasDispatcher implements HasDispatcherInterface 
{
	protected $eventDispatcher = NULL;
	public static function getAllEvents() 
	{
		return array( );
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
		return $this->getEventDispatcher()->dispatch($eventName, new Event($context));
	}
	public function addSubscriber(\Symfony\Component\EventDispatcher\EventSubscriberInterface $subscriber) 
	{
		$this->getEventDispatcher()->addSubscriber($subscriber);
		return $this;
	}
}
?>