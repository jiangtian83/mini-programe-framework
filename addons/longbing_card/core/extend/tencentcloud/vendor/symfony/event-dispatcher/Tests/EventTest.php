<?php  namespace Symfony\Component\EventDispatcher\Tests;
class EventTest extends \PHPUnit\Framework\TestCase 
{
	protected $event = NULL;
	protected $dispatcher = NULL;
	protected function setUp() 
	{
		$this->event = new \Symfony\Component\EventDispatcher\Event();
		$this->dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
	}
	protected function tearDown() 
	{
		$this->event = null;
		$this->dispatcher = null;
	}
	public function testIsPropagationStopped() 
	{
		$this->assertFalse($this->event->isPropagationStopped());
	}
	public function testStopPropagationAndIsPropagationStopped() 
	{
		$this->event->stopPropagation();
		$this->assertTrue($this->event->isPropagationStopped());
	}
	public function testLegacySetDispatcher() 
	{
		$this->event->setDispatcher($this->dispatcher);
		$this->assertSame($this->dispatcher, $this->event->getDispatcher());
	}
	public function testLegacyGetDispatcher() 
	{
		$this->assertNull($this->event->getDispatcher());
	}
	public function testLegacyGetName() 
	{
		$this->assertNull($this->event->getName());
	}
	public function testLegacySetName() 
	{
		$this->event->setName("foo");
		$this->assertEquals("foo", $this->event->getName());
	}
}
?>