<?php  namespace Symfony\Component\EventDispatcher;
interface EventDispatcherInterface 
{
	public function dispatch($eventName, Event $event);
	public function addListener($eventName, $listener, $priority);
	public function addSubscriber(EventSubscriberInterface $subscriber);
	public function removeListener($eventName, $listener);
	public function removeSubscriber(EventSubscriberInterface $subscriber);
	public function getListeners($eventName);
	public function hasListeners($eventName);
}
?>