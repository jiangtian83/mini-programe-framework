<?php  namespace Guzzle\Tests\Common;
class AbstractHasAdapterTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testDoesNotRequireRegisteredEvents() 
	{
		$this->assertEquals(array( ), \Guzzle\Common\AbstractHasDispatcher::getAllEvents());
	}
	public function testAllowsDispatcherToBeInjected() 
	{
		$d = new \Symfony\Component\EventDispatcher\EventDispatcher();
		$mock = $this->getMockForAbstractClass("Guzzle\\Common\\AbstractHasDispatcher");
		$this->assertSame($mock, $mock->setEventDispatcher($d));
		$this->assertSame($d, $mock->getEventDispatcher());
	}
	public function testCreatesDefaultEventDispatcherIfNeeded() 
	{
		$mock = $this->getMockForAbstractClass("Guzzle\\Common\\AbstractHasDispatcher");
		$this->assertInstanceOf("Symfony\\Component\\EventDispatcher\\EventDispatcher", $mock->getEventDispatcher());
	}
	public function testHelperDispatchesEvents() 
	{
		$data = array( );
		$mock = $this->getMockForAbstractClass("Guzzle\\Common\\AbstractHasDispatcher");
		$mock->getEventDispatcher()->addListener("test", function(\Guzzle\Common\Event $e) use (&$data) 
		{
			$data = $e->getIterator()->getArrayCopy();
		}
		);
		$mock->dispatch("test", array( "param" => "abc" ));
		$this->assertEquals(array( "param" => "abc" ), $data);
	}
	public function testHelperAttachesSubscribers() 
	{
		$mock = $this->getMockForAbstractClass("Guzzle\\Common\\AbstractHasDispatcher");
		$subscriber = $this->getMockForAbstractClass("Symfony\\Component\\EventDispatcher\\EventSubscriberInterface");
		$dispatcher = $this->getMockBuilder("Symfony\\Component\\EventDispatcher\\EventDispatcher")->setMethods(array( "addSubscriber" ))->getMock();
		$dispatcher->expects($this->once())->method("addSubscriber");
		$mock->setEventDispatcher($dispatcher);
		$mock->addSubscriber($subscriber);
	}
}
?>