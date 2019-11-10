<?php  namespace Symfony\Component\EventDispatcher\Tests\DependencyInjection;
class RegisterListenersPassTest extends \PHPUnit\Framework\TestCase 
{
	public function testEventSubscriberWithoutInterface() 
	{
		$builder = new \Symfony\Component\DependencyInjection\ContainerBuilder();
		$builder->register("event_dispatcher");
		$builder->register("my_event_subscriber", "stdClass")->addTag("kernel.event_subscriber");
		$registerListenersPass = new \Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass();
		$registerListenersPass->process($builder);
	}
	public function testValidEventSubscriber() 
	{
		$services = array( "my_event_subscriber" => array( array( ) ) );
		$builder = new \Symfony\Component\DependencyInjection\ContainerBuilder();
		$eventDispatcherDefinition = $builder->register("event_dispatcher");
		$builder->register("my_event_subscriber", "Symfony\\Component\\EventDispatcher\\Tests\\DependencyInjection\\SubscriberService")->addTag("kernel.event_subscriber");
		$registerListenersPass = new \Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass();
		$registerListenersPass->process($builder);
		$this->assertEquals(array( array( "addSubscriberService", array( "my_event_subscriber", "Symfony\\Component\\EventDispatcher\\Tests\\DependencyInjection\\SubscriberService" ) ) ), $eventDispatcherDefinition->getMethodCalls());
	}
	public function testPrivateEventListener() 
	{
		$container = new \Symfony\Component\DependencyInjection\ContainerBuilder();
		$container->register("foo", "stdClass")->setPublic(false)->addTag("kernel.event_listener", array( ));
		$container->register("event_dispatcher", "stdClass");
		$registerListenersPass = new \Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass();
		$registerListenersPass->process($container);
	}
	public function testPrivateEventSubscriber() 
	{
		$container = new \Symfony\Component\DependencyInjection\ContainerBuilder();
		$container->register("foo", "stdClass")->setPublic(false)->addTag("kernel.event_subscriber", array( ));
		$container->register("event_dispatcher", "stdClass");
		$registerListenersPass = new \Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass();
		$registerListenersPass->process($container);
	}
	public function testAbstractEventListener() 
	{
		$container = new \Symfony\Component\DependencyInjection\ContainerBuilder();
		$container->register("foo", "stdClass")->setAbstract(true)->addTag("kernel.event_listener", array( ));
		$container->register("event_dispatcher", "stdClass");
		$registerListenersPass = new \Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass();
		$registerListenersPass->process($container);
	}
	public function testAbstractEventSubscriber() 
	{
		$container = new \Symfony\Component\DependencyInjection\ContainerBuilder();
		$container->register("foo", "stdClass")->setAbstract(true)->addTag("kernel.event_subscriber", array( ));
		$container->register("event_dispatcher", "stdClass");
		$registerListenersPass = new \Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass();
		$registerListenersPass->process($container);
	}
	public function testEventSubscriberResolvableClassName() 
	{
		$container = new \Symfony\Component\DependencyInjection\ContainerBuilder();
		$container->setParameter("subscriber.class", "Symfony\\Component\\EventDispatcher\\Tests\\DependencyInjection\\SubscriberService");
		$container->register("foo", "%subscriber.class%")->addTag("kernel.event_subscriber", array( ));
		$container->register("event_dispatcher", "stdClass");
		$registerListenersPass = new \Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass();
		$registerListenersPass->process($container);
		$definition = $container->getDefinition("event_dispatcher");
		$expected_calls = array( array( "addSubscriberService", array( "foo", "Symfony\\Component\\EventDispatcher\\Tests\\DependencyInjection\\SubscriberService" ) ) );
		$this->assertSame($expected_calls, $definition->getMethodCalls());
	}
	public function testEventSubscriberUnresolvableClassName() 
	{
		$container = new \Symfony\Component\DependencyInjection\ContainerBuilder();
		$container->register("foo", "%subscriber.class%")->addTag("kernel.event_subscriber", array( ));
		$container->register("event_dispatcher", "stdClass");
		$registerListenersPass = new \Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass();
		$registerListenersPass->process($container);
	}
}
class SubscriberService implements \Symfony\Component\EventDispatcher\EventSubscriberInterface 
{
	public static function getSubscribedEvents() 
	{
	}
}
?>