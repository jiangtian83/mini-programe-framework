<?php  namespace Guzzle\Tests\Plugin\Backoff;
class AbstractBackoffStrategyTest extends \Guzzle\Tests\GuzzleTestCase 
{
	protected function getMockStrategy() 
	{
		return $this->getMockBuilder("Guzzle\\Plugin\\Backoff\\AbstractBackoffStrategy")->setMethods(array( "getDelay", "makesDecision" ))->getMockForAbstractClass();
	}
	public function testReturnsZeroWhenNoNextAndGotNull() 
	{
		$request = new \Guzzle\Http\Message\Request("GET", "http://www.foo.com");
		$mock = $this->getMockStrategy();
		$mock->expects($this->atLeastOnce())->method("getDelay")->will($this->returnValue(null));
		$this->assertEquals(0, $mock->getBackoffPeriod(0, $request));
	}
	public function testReturnsFalse() 
	{
		$request = new \Guzzle\Http\Message\Request("GET", "http://www.foo.com");
		$mock = $this->getMockStrategy();
		$mock->expects($this->atLeastOnce())->method("getDelay")->will($this->returnValue(false));
		$this->assertEquals(false, $mock->getBackoffPeriod(0, $request));
	}
	public function testReturnsNextValueWhenNullOrTrue() 
	{
		$request = new \Guzzle\Http\Message\Request("GET", "http://www.foo.com");
		$mock = $this->getMockStrategy();
		$mock->expects($this->atLeastOnce())->method("getDelay")->will($this->returnValue(null));
		$mock->expects($this->any())->method("makesDecision")->will($this->returnValue(false));
		$mock2 = $this->getMockStrategy();
		$mock2->expects($this->atLeastOnce())->method("getDelay")->will($this->returnValue(10));
		$mock2->expects($this->atLeastOnce())->method("makesDecision")->will($this->returnValue(true));
		$mock->setNext($mock2);
		$this->assertEquals(10, $mock->getBackoffPeriod(0, $request));
	}
	public function testReturnsFalseWhenNullAndNoNext() 
	{
		$request = new \Guzzle\Http\Message\Request("GET", "http://www.foo.com");
		$s = new \Guzzle\Plugin\Backoff\TruncatedBackoffStrategy(2);
		$this->assertFalse($s->getBackoffPeriod(0, $request));
	}
	public function testHasNext() 
	{
		$a = new \Guzzle\Plugin\Backoff\TruncatedBackoffStrategy(2);
		$b = new \Guzzle\Plugin\Backoff\TruncatedBackoffStrategy(2);
		$a->setNext($b);
		$this->assertSame($b, $a->getNext());
	}
	public function testSkipsOtherDecisionsInChainWhenOneReturnsTrue() 
	{
		$a = new \Guzzle\Plugin\Backoff\CallbackBackoffStrategy(function() 
		{
			return null;
		}
		, true);
		$b = new \Guzzle\Plugin\Backoff\CallbackBackoffStrategy(function() 
		{
			return true;
		}
		, true);
		$c = new \Guzzle\Plugin\Backoff\CallbackBackoffStrategy(function() 
		{
			return null;
		}
		, true);
		$d = new \Guzzle\Plugin\Backoff\CallbackBackoffStrategy(function() 
		{
			return 10;
		}
		, false);
		$a->setNext($b);
		$b->setNext($c);
		$c->setNext($d);
		$this->assertEquals(10, $a->getBackoffPeriod(2, new \Guzzle\Http\Message\Request("GET", "http://www.foo.com")));
	}
	public function testReturnsZeroWhenDecisionMakerReturnsTrueButNoFurtherStrategiesAreInTheChain() 
	{
		$a = new \Guzzle\Plugin\Backoff\CallbackBackoffStrategy(function() 
		{
			return null;
		}
		, true);
		$b = new \Guzzle\Plugin\Backoff\CallbackBackoffStrategy(function() 
		{
			return true;
		}
		, true);
		$a->setNext($b);
		$this->assertSame(0, $a->getBackoffPeriod(2, new \Guzzle\Http\Message\Request("GET", "http://www.foo.com")));
	}
}
?>