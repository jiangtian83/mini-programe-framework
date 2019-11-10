<?php  namespace Symfony\Component\EventDispatcher\Tests;
class EventDispatcherTest extends AbstractEventDispatcherTest 
{
	protected function createEventDispatcher() 
	{
		return new \Symfony\Component\EventDispatcher\EventDispatcher();
	}
}
?>