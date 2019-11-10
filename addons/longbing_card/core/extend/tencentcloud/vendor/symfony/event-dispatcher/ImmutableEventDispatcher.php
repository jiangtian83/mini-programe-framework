<?php  namespace Symfony\Component\EventDispatcher;
class ImmutableEventDispatcher implements EventDispatcherInterface 
{
	private $dispatcher = NULL;
	public function __construct(EventDispatcherInterface $dispatcher) 
	{
		$this->dispatcher = $dispatcher;
	}
	public function dispatch($eventName, Event $event = NULL) 
	{
		return $this->dispatcher->dispatch($eventName, $event);
	}
	public function addListener($eventName, $listener, $priority = 0) 
	{
		throw new \BadMethodCallException("Unmodifiable event dispatchers must not be modified.");
	}
	public function addSubscriber(EventSubscriberInterface $subscriber) 
	{
		throw new \BadMethodCallException("Unmodifiable event dispatchers must not be modified.");
	}
	public function removeListener($eventName, $listener) 
	{
		throw new \BadMethodCallException("Unmodifiable event dispatchers must not be modified.");
	}
	public function removeSubscriber(EventSubscriberInterface $subscriber) 
	{
		throw new \BadMethodCallException("Unmodifiable event dispatchers must not be modified.");
	}
	public function getListeners($eventName = NULL) 
	{
		return $this->dispatcher->getListeners($eventName);
	}
	public function getListenerPriority($eventName, $listener) 
	{
		return $this->dispatcher->getListenerPriority($eventName, $listener);
	}
	public function hasListeners($eventName = NULL) 
	{
		return $this->dispatcher->hasListeners($eventName);
	}
}
?>