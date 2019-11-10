<?php  namespace Guzzle\Tests\Plugin\Backoff;
class CallbackBackoffStrategyTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testEnsuresIsCallable() 
	{
		$strategy = new \Guzzle\Plugin\Backoff\CallbackBackoffStrategy(new \stdClass(), true);
	}
	public function testRetriesWithCallable() 
	{
		$request = $this->getMock("Guzzle\\Http\\Message\\Request", array( ), array( ), "", false);
		$strategy = new \Guzzle\Plugin\Backoff\CallbackBackoffStrategy(function() 
		{
			return 10;
		}
		, true);
		$this->assertTrue($strategy->makesDecision());
		$this->assertEquals(10, $strategy->getBackoffPeriod(0, $request));
		$strategy = new \Guzzle\Plugin\Backoff\CallbackBackoffStrategy(function() 
		{
			return null;
		}
		, false);
		$this->assertFalse($strategy->makesDecision());
		$this->assertFalse($strategy->getBackoffPeriod(0, $request));
	}
}
?>