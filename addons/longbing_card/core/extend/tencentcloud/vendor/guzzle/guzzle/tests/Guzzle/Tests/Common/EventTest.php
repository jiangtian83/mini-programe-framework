<?php  namespace Guzzle\Tests\Common;
class EventTest extends \Guzzle\Tests\GuzzleTestCase 
{
	private function getEvent() 
	{
		return new \Guzzle\Common\Event(array( "test" => "123", "other" => "456", "event" => "test.notify" ));
	}
	public function testAllowsParameterInjection() 
	{
		$event = new \Guzzle\Common\Event(array( "test" => "123" ));
		$this->assertEquals("123", $event["test"]);
	}
	public function testImplementsArrayAccess() 
	{
		$event = $this->getEvent();
		$this->assertEquals("123", $event["test"]);
		$this->assertNull($event["foobar"]);
		$this->assertTrue($event->offsetExists("test"));
		$this->assertFalse($event->offsetExists("foobar"));
		unset($event["test"]);
		$this->assertFalse($event->offsetExists("test"));
		$event["test"] = "new";
		$this->assertEquals("new", $event["test"]);
	}
	public function testImplementsIteratorAggregate() 
	{
		$event = $this->getEvent();
		$this->assertInstanceOf("ArrayIterator", $event->getIterator());
	}
	public function testConvertsToArray() 
	{
		$this->assertEquals(array( "test" => "123", "other" => "456", "event" => "test.notify" ), $this->getEvent()->toArray());
	}
}
?>