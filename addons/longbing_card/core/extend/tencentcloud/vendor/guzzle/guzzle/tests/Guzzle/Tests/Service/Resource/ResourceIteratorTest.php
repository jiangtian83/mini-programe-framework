<?php  namespace Guzzle\Tests\Service\Resource;
class ResourceIteratorTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testDescribesEvents() 
	{
		$this->assertInternalType("array", \Guzzle\Service\Resource\ResourceIterator::getAllEvents());
	}
	public function testConstructorConfiguresDefaults() 
	{
		$ri = $this->getMockForAbstractClass("Guzzle\\Service\\Resource\\ResourceIterator", array( $this->getServiceBuilder()->get("mock")->getCommand("iterable_command"), array( "limit" => 10, "page_size" => 3 ) ), "MockIterator");
		$this->assertEquals(false, $ri->getNextToken());
		$this->assertEquals(false, $ri->current());
	}
	public function testSendsRequestsForNextSetOfResources() 
	{
		$this->getServer()->flush();
		$this->getServer()->enqueue(array( "HTTP/1.1 200 OK\r\nContent-Length: 52\r\n\r\n{ \"next_token\": \"g\", \"resources\": [\"d\", \"e\", \"f\"] }", "HTTP/1.1 200 OK\r\nContent-Length: 52\r\n\r\n{ \"next_token\": \"j\", \"resources\": [\"g\", \"h\", \"i\"] }", "HTTP/1.1 200 OK\r\nContent-Length: 41\r\n\r\n{ \"next_token\": \"\", \"resources\": [\"j\"] }" ));
		$ri = new \Guzzle\Tests\Service\Mock\Model\MockCommandIterator($this->getServiceBuilder()->get("mock")->getCommand("iterable_command"), array( "page_size" => 3 ));
		$this->assertEquals(0, count($this->getServer()->getReceivedRequests(false)));
		$ri->toArray();
		$requests = $this->getServer()->getReceivedRequests(true);
		$this->assertEquals(3, count($requests));
		$this->assertEquals(3, $requests[0]->getQuery()->get("page_size"));
		$this->assertEquals(3, $requests[1]->getQuery()->get("page_size"));
		$this->assertEquals(3, $requests[2]->getQuery()->get("page_size"));
		$this->getServer()->flush();
		$this->getServer()->enqueue(array( "HTTP/1.1 200 OK\r\nContent-Length: 52\r\n\r\n{ \"next_token\": \"g\", \"resources\": [\"d\", \"e\", \"f\"] }", "HTTP/1.1 200 OK\r\nContent-Length: 52\r\n\r\n{ \"next_token\": \"j\", \"resources\": [\"g\", \"h\", \"i\"] }", "HTTP/1.1 200 OK\r\nContent-Length: 41\r\n\r\n{ \"next_token\": \"\", \"resources\": [\"j\"] }" ));
		$d = array( );
		foreach( $ri as $data ) 
		{
			$d[] = $data;
		}
		$this->assertEquals(array( "d", "e", "f", "g", "h", "i", "j" ), $d);
	}
	public function testCalculatesPageSize() 
	{
		$this->getServer()->flush();
		$this->getServer()->enqueue(array( "HTTP/1.1 200 OK\r\nContent-Length: 52\r\n\r\n{ \"next_token\": \"g\", \"resources\": [\"d\", \"e\", \"f\"] }", "HTTP/1.1 200 OK\r\nContent-Length: 52\r\n\r\n{ \"next_token\": \"j\", \"resources\": [\"g\", \"h\", \"i\"] }", "HTTP/1.1 200 OK\r\nContent-Length: 52\r\n\r\n{ \"next_token\": \"j\", \"resources\": [\"j\", \"k\"] }" ));
		$ri = new \Guzzle\Tests\Service\Mock\Model\MockCommandIterator($this->getServiceBuilder()->get("mock")->getCommand("iterable_command"), array( "page_size" => 3, "limit" => 7 ));
		$this->assertEquals(array( "d", "e", "f", "g", "h", "i", "j" ), $ri->toArray());
		$requests = $this->getServer()->getReceivedRequests(true);
		$this->assertEquals(3, count($requests));
		$this->assertEquals(3, $requests[0]->getQuery()->get("page_size"));
		$this->assertEquals(3, $requests[1]->getQuery()->get("page_size"));
		$this->assertEquals(1, $requests[2]->getQuery()->get("page_size"));
	}
	public function testUseAsArray() 
	{
		$this->getServer()->flush();
		$this->getServer()->enqueue(array( "HTTP/1.1 200 OK\r\nContent-Length: 52\r\n\r\n{ \"next_token\": \"g\", \"resources\": [\"d\", \"e\", \"f\"] }", "HTTP/1.1 200 OK\r\nContent-Length: 52\r\n\r\n{ \"next_token\": \"\", \"resources\": [\"g\", \"h\", \"i\"] }" ));
		$ri = new \Guzzle\Tests\Service\Mock\Model\MockCommandIterator($this->getServiceBuilder()->get("mock")->getCommand("iterable_command"));
		$this->assertEquals(0, $ri->key());
		$this->assertEquals(0, count($ri));
		$data = array( );
		foreach( $ri as $key => $value ) 
		{
			$data[$key] = $value;
		}
		$this->assertEquals(6, count($ri));
		$this->assertEquals(array( "d", "e", "f", "g", "h", "i" ), $data);
	}
	public function testBailsWhenSendReturnsNoResults() 
	{
		$this->getServer()->flush();
		$this->getServer()->enqueue(array( "HTTP/1.1 200 OK\r\n\r\n{ \"next_token\": \"g\", \"resources\": [\"d\", \"e\", \"f\"] }", "HTTP/1.1 200 OK\r\n\r\n{ \"next_token\": \"\", \"resources\": [] }" ));
		$ri = new \Guzzle\Tests\Service\Mock\Model\MockCommandIterator($this->getServiceBuilder()->get("mock")->getCommand("iterable_command"));
		$data = $ri->toArray();
		$this->assertEquals(3, count($ri));
		$this->assertEquals(array( "d", "e", "f" ), $data);
		$this->assertEquals(2, $ri->getRequestCount());
	}
	public function testHoldsDataOptions() 
	{
		$ri = new \Guzzle\Tests\Service\Mock\Model\MockCommandIterator($this->getServiceBuilder()->get("mock")->getCommand("iterable_command"));
		$this->assertNull($ri->get("foo"));
		$this->assertSame($ri, $ri->set("foo", "bar"));
		$this->assertEquals("bar", $ri->get("foo"));
	}
	public function testSettingLimitOrPageSizeClearsData() 
	{
		$this->getServer()->flush();
		$this->getServer()->enqueue(array( "HTTP/1.1 200 OK\r\n\r\n{ \"next_token\": \"\", \"resources\": [\"d\", \"e\", \"f\"] }", "HTTP/1.1 200 OK\r\n\r\n{ \"next_token\": \"\", \"resources\": [\"d\", \"e\", \"f\"] }", "HTTP/1.1 200 OK\r\n\r\n{ \"next_token\": \"\", \"resources\": [\"d\", \"e\", \"f\"] }" ));
		$ri = new \Guzzle\Tests\Service\Mock\Model\MockCommandIterator($this->getServiceBuilder()->get("mock")->getCommand("iterable_command"));
		$ri->toArray();
		$this->assertNotEmpty($this->readAttribute($ri, "resources"));
		$ri->setLimit(10);
		$this->assertEmpty($this->readAttribute($ri, "resources"));
		$ri->toArray();
		$this->assertNotEmpty($this->readAttribute($ri, "resources"));
		$ri->setPageSize(10);
		$this->assertEmpty($this->readAttribute($ri, "resources"));
	}
	public function testWorksWithCustomAppendIterator() 
	{
		$this->getServer()->flush();
		$this->getServer()->enqueue(array( "HTTP/1.1 200 OK\r\n\r\n{ \"next_token\": \"\", \"resources\": [\"d\", \"e\", \"f\"] }" ));
		$ri = new \Guzzle\Tests\Service\Mock\Model\MockCommandIterator($this->getServiceBuilder()->get("mock")->getCommand("iterable_command"));
		$a = new \Guzzle\Iterator\AppendIterator();
		$a->append($ri);
		$results = iterator_to_array($a, false);
		$this->assertEquals(4, $ri->calledNext);
	}
}
?>