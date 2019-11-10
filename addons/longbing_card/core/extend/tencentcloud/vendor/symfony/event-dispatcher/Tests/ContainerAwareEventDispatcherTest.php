<?php  namespace Symfony\Component\EventDispatcher\Tests;
class ContainerAwareEventDispatcherTest extends AbstractEventDispatcherTest 
{
	protected function createEventDispatcher() 
	{
		$container = new \Symfony\Component\DependencyInjection\Container();
		return new \Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher($container);
	}
	public function testAddAListenerService() 
	{
		$event = new \Symfony\Component\EventDispatcher\Event();
		$service = $this->getMockBuilder("Symfony\\Component\\EventDispatcher\\Tests\\Service")->getMock();
		$service->expects($this->once())->method("onEvent")->with($event);
		$container = new \Symfony\Component\DependencyInjection\Container();
		$container->set("service.listener", $service);
		$dispatcher = new \Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher($container);
		$dispatcher->addListenerService("onEvent", array( "service.listener", "onEvent" ));
		$dispatcher->dispatch("onEvent", $event);
	}
	public function testAddASubscriberService() 
	{
		$event = new \Symfony\Component\EventDispatcher\Event();
		$service = $this->getMockBuilder("Symfony\\Component\\EventDispatcher\\Tests\\SubscriberService")->getMock();
		$service->expects($this->once())->method("onEvent")->with($event);
		$service->expects($this->once())->method("onEventWithPriority")->with($event);
		$service->expects($this->once())->method("onEventNested")->with($event);
		$container = new \Symfony\Component\DependencyInjection\Container();
		$container->set("service.subscriber", $service);
		$dispatcher = new \Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher($container);
		$dispatcher->addSubscriberService("service.subscriber", "Symfony\\Component\\EventDispatcher\\Tests\\SubscriberService");
		$dispatcher->dispatch("onEvent", $event);
		$dispatcher->dispatch("onEventWithPriority", $event);
		$dispatcher->dispatch("onEventNested", $event);
	}
	public function testPreventDuplicateListenerService() 
	{
		$event = new \Symfony\Component\EventDispatcher\Event();
		$service = $this->getMockBuilder("Symfony\\Component\\EventDispatcher\\Tests\\Service")->getMock();
		$service->expects($this->once())->method("onEvent")->with($event);
		$container = new \Symfony\Component\DependencyInjection\Container();
		$container->set("service.listener", $service);
		$dispatcher = new \Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher($container);
		$dispatcher->addListenerService("onEvent", array( "service.listener", "onEvent" ), 5);
		$dispatcher->addListenerService("onEvent", array( "service.listener", "onEvent" ), 10);
		$dispatcher->dispatch("onEvent", $event);
	}
	public function testTriggerAListenerServiceOutOfScope() 
	{
		$service = $this->getMockBuilder("Symfony\\Component\\EventDispatcher\\Tests\\Service")->getMock();
		$scope = new \Symfony\Component\DependencyInjection\Scope("scope");
		$container = new \Symfony\Component\DependencyInjection\Container();
		$container->addScope($scope);
		$container->enterScope("scope");
		$container->set("service.listener", $service, "scope");
		$dispatcher = new \Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher($container);
		$dispatcher->addListenerService("onEvent", array( "service.listener", "onEvent" ));
		$container->leaveScope("scope");
		$dispatcher->dispatch("onEvent");
	}
	public function testReEnteringAScope() 
	{
		$event = new \Symfony\Component\EventDispatcher\Event();
		$service1 = $this->getMockBuilder("Symfony\\Component\\EventDispatcher\\Tests\\Service")->getMock();
		$service1->expects($this->exactly(2))->method("onEvent")->with($event);
		$scope = new \Symfony\Component\DependencyInjection\Scope("scope");
		$container = new \Symfony\Component\DependencyInjection\Container();
		$container->addScope($scope);
		$container->enterScope("scope");
		$container->set("service.listener", $service1, "scope");
		$dispatcher = new \Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher($container);
		$dispatcher->addListenerService("onEvent", array( "service.listener", "onEvent" ));
		$dispatcher->dispatch("onEvent", $event);
		$service2 = $this->getMockBuilder("Symfony\\Component\\EventDispatcher\\Tests\\Service")->getMock();
		$service2->expects($this->once())->method("onEvent")->with($event);
		$container->enterScope("scope");
		$container->set("service.listener", $service2, "scope");
		$dispatcher->dispatch("onEvent", $event);
		$container->leaveScope("scope");
		$dispatcher->dispatch("onEvent");
	}
	public function testHasListenersOnLazyLoad() 
	{
		$event = new \Symfony\Component\EventDispatcher\Event();
		$service = $this->getMockBuilder("Symfony\\Component\\EventDispatcher\\Tests\\Service")->getMock();
		$container = new \Symfony\Component\DependencyInjection\Container();
		$container->set("service.listener", $service);
		$dispatcher = new \Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher($container);
		$dispatcher->addListenerService("onEvent", array( "service.listener", "onEvent" ));
		$event->setDispatcher($dispatcher);
		$event->setName("onEvent");
		$service->expects($this->once())->method("onEvent")->with($event);
		$this->assertTrue($dispatcher->hasListeners());
		if( $dispatcher->hasListeners("onEvent") ) 
		{
			$dispatcher->dispatch("onEvent");
		}
	}
	public function testGetListenersOnLazyLoad() 
	{
		$service = $this->getMockBuilder("Symfony\\Component\\EventDispatcher\\Tests\\Service")->getMock();
		$container = new \Symfony\Component\DependencyInjection\Container();
		$container->set("service.listener", $service);
		$dispatcher = new \Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher($container);
		$dispatcher->addListenerService("onEvent", array( "service.listener", "onEvent" ));
		$listeners = $dispatcher->getListeners();
		$this->assertArrayHasKey("onEvent", $listeners);
		$this->assertCount(1, $dispatcher->getListeners("onEvent"));
	}
	public function testRemoveAfterDispatch() 
	{
		$service = $this->getMockBuilder("Symfony\\Component\\EventDispatcher\\Tests\\Service")->getMock();
		$container = new \Symfony\Component\DependencyInjection\Container();
		$container->set("service.listener", $service);
		$dispatcher = new \Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher($container);
		$dispatcher->addListenerService("onEvent", array( "service.listener", "onEvent" ));
		$dispatcher->dispatch("onEvent", new \Symfony\Component\EventDispatcher\Event());
		$dispatcher->removeListener("onEvent", array( $container->get("service.listener"), "onEvent" ));
		$this->assertFalse($dispatcher->hasListeners("onEvent"));
	}
	public function testRemoveBeforeDispatch() 
	{
		$service = $this->getMockBuilder("Symfony\\Component\\EventDispatcher\\Tests\\Service")->getMock();
		$container = new \Symfony\Component\DependencyInjection\Container();
		$container->set("service.listener", $service);
		$dispatcher = new \Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher($container);
		$dispatcher->addListenerService("onEvent", array( "service.listener", "onEvent" ));
		$dispatcher->removeListener("onEvent", array( $container->get("service.listener"), "onEvent" ));
		$this->assertFalse($dispatcher->hasListeners("onEvent"));
	}
}
class SubscriberService implements \Symfony\Component\EventDispatcher\EventSubscriberInterface 
{
	public static function getSubscribedEvents() 
	{
		return array( "onEvent" => "onEvent", "onEventWithPriority" => array( "onEventWithPriority", 10 ), "onEventNested" => array( array( "onEventNested" ) ) );
	}
	public function onEvent(\Symfony\Component\EventDispatcher\Event $e) 
	{
	}
	public function onEventWithPriority(\Symfony\Component\EventDispatcher\Event $e) 
	{
	}
	public function onEventNested(\Symfony\Component\EventDispatcher\Event $e) 
	{
	}
}
class Service 
{
	public function onEvent(\Symfony\Component\EventDispatcher\Event $e) 
	{
	}
}
?>