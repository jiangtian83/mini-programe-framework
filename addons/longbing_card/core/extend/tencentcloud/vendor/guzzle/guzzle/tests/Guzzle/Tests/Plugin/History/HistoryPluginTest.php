<?php  namespace Guzzle\Tests\Plugin\History;
class HistoryPluginTest extends \Guzzle\Tests\GuzzleTestCase 
{
	protected function addRequests(\Guzzle\Plugin\History\HistoryPlugin $h, $num) 
	{
		$requests = array( );
		$client = new \Guzzle\Http\Client("http://127.0.0.1/");
		for( $i = 0; $i < $num; $i++ ) 
		{
			$requests[$i] = $client->get();
			$requests[$i]->setResponse(new \Guzzle\Http\Message\Response(200), true);
			$requests[$i]->send();
			$h->add($requests[$i]);
		}
		return $requests;
	}
	public function testDescribesSubscribedEvents() 
	{
		$this->assertInternalType("array", \Guzzle\Plugin\History\HistoryPlugin::getSubscribedEvents());
	}
	public function testMaintainsLimitValue() 
	{
		$h = new \Guzzle\Plugin\History\HistoryPlugin();
		$this->assertSame($h, $h->setLimit(10));
		$this->assertEquals(10, $h->getLimit());
	}
	public function testAddsRequests() 
	{
		$h = new \Guzzle\Plugin\History\HistoryPlugin();
		$requests = $this->addRequests($h, 1);
		$this->assertEquals(1, count($h));
		$i = $h->getIterator();
		$this->assertEquals(1, count($i));
		$this->assertEquals($requests[0], $i[0]);
	}
	public function testMaintainsLimit() 
	{
		$h = new \Guzzle\Plugin\History\HistoryPlugin();
		$h->setLimit(2);
		$requests = $this->addRequests($h, 3);
		$this->assertEquals(2, count($h));
		$i = 0;
		foreach( $h as $request ) 
		{
			if( 0 < $i ) 
			{
				$this->assertSame($requests[$i], $request);
			}
		}
	}
	public function testReturnsLastRequest() 
	{
		$h = new \Guzzle\Plugin\History\HistoryPlugin();
		$requests = $this->addRequests($h, 5);
		$this->assertSame(end($requests), $h->getLastRequest());
	}
	public function testReturnsLastResponse() 
	{
		$h = new \Guzzle\Plugin\History\HistoryPlugin();
		$requests = $this->addRequests($h, 5);
		$this->assertSame(end($requests)->getResponse(), $h->getLastResponse());
	}
	public function testClearsHistory() 
	{
		$h = new \Guzzle\Plugin\History\HistoryPlugin();
		$requests = $this->addRequests($h, 5);
		$this->assertEquals(5, count($h));
		$h->clear();
		$this->assertEquals(0, count($h));
	}
	public function testUpdatesAddRequests() 
	{
		$h = new \Guzzle\Plugin\History\HistoryPlugin();
		$client = new \Guzzle\Http\Client("http://127.0.0.1/");
		$client->getEventDispatcher()->addSubscriber($h);
		$request = $client->get();
		$request->setResponse(new \Guzzle\Http\Message\Response(200), true);
		$request->send();
		$this->assertSame($request, $h->getLastRequest());
	}
	public function testCanCastToString() 
	{
		$client = new \Guzzle\Http\Client("http://127.0.0.1/");
		$h = new \Guzzle\Plugin\History\HistoryPlugin();
		$client->getEventDispatcher()->addSubscriber($h);
		$mock = new \Guzzle\Plugin\Mock\MockPlugin(array( new \Guzzle\Http\Message\Response(301, array( "Location" => "/redirect1", "Content-Length" => 0 )), new \Guzzle\Http\Message\Response(307, array( "Location" => "/redirect2", "Content-Length" => 0 )), new \Guzzle\Http\Message\Response(200, array( "Content-Length" => "2" ), "HI") ));
		$client->getEventDispatcher()->addSubscriber($mock);
		$request = $client->get();
		$request->send();
		$this->assertEquals(3, count($h));
		$this->assertEquals(3, count($mock->getReceivedRequests()));
		$h = str_replace("\r", "", $h);
		$this->assertContains("> GET / HTTP/1.1\nHost: 127.0.0.1\nUser-Agent:", $h);
		$this->assertContains("< HTTP/1.1 301 Moved Permanently\nLocation: /redirect1", $h);
		$this->assertContains("< HTTP/1.1 307 Temporary Redirect\nLocation: /redirect2", $h);
		$this->assertContains("< HTTP/1.1 200 OK\nContent-Length: 2\n\nHI", $h);
	}
}
?>