<?php  namespace Guzzle\Common;
interface HasDispatcherInterface 
{
	public static function getAllEvents();
	public function setEventDispatcher(\Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher);
	public function getEventDispatcher();
	public function dispatch($eventName, array $context);
	public function addSubscriber(\Symfony\Component\EventDispatcher\EventSubscriberInterface $subscriber);
}
?>