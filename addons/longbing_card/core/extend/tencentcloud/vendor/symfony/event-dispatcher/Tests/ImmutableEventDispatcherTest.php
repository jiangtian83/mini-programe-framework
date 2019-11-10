<?php  namespace Symfony\Component\EventDispatcher\Tests;
class ImmutableEventDispatcherTest extends \PHPUnit\Framework\TestCase 
{
	private $innerDispatcher = NULL;
	private $dispatcher = NULL;
	protected function setUp() 
	{
		$this->innerDispatcher = $this->getMockBuilder("Symfony\\Component\\EventDispatcher\\EventDispatcherInterface")->getMock();
		$this->dispatcher = new \Symfony\Component\EventDispatcher\ImmutableEventDispatcher($this->innerDispatcher);
	}
	public function testDispatchDelegates() 
	{
		$event = new \Symfony\Component\EventDispatcher\Event();
		$this->innerDispatcher->expects($this->once())->method("dispatch")->with("event", $event)->will($this->returnValue("result"));
		$this->assertSame("result", $this->dispatcher->dispatch("event", $event));
	}
	public function testGetListenersDelegates() 
	{
		$this->innerDispatcher->expects($this->once())->method("getListeners")->with("event")->will($this->returnValue("result"));
		$this->assertSame("result", $this->dispatcher->getListeners("event"));
	}
	public function testHasListenersDelegates() 
	{
		$this->innerDispatcher->expects($this->once())->method("hasListeners")->with("event")->will($this->returnValue("result"));
		$this->assertSame("result", $this->dispatcher->hasListeners("event"));
	}
	public function testAddListenerDisallowed() 
	{
		$this->dispatcher->addListener("event", function() 
		{
			return "foo";
		}
		);
	}
	public function testAddSubscriberDisallowed() 
	{
		$subscriber = $this->getMockBuilder("Symfony\\Component\\EventDispatcher\\EventSubscriberInterface")->getMock();
		$this->dispatcher->addSubscriber($subscriber);
	}
	public function testRemoveListenerDisallowed() 
	{
		$this->dispatcher->removeListener("event", function() 
		{
			return "foo";
		}
		);
	}
	public function testRemoveSubscriberDisallowed() 
	{
		$subscriber = $this->getMockBuilder("Symfony\\Component\\EventDispatcher\\EventSubscriberInterface")->getMock();
		$this->dispatcher->removeSubscriber($subscriber);
	}
}
?>