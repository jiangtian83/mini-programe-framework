<?php  namespace Guzzle\Tests\Log;
class ClosureLogAdapterTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testClosure() 
	{
		$that = $this;
		$modified = null;
		$this->adapter = new \Guzzle\Log\ClosureLogAdapter(function($message, $priority, $extras = NULL) use ($that, &$modified) 
		{
			$modified = array( $message, $priority, $extras );
		}
		);
		$this->adapter->log("test", LOG_NOTICE, "127.0.0.1");
		$this->assertEquals(array( "test", LOG_NOTICE, "127.0.0.1" ), $modified);
	}
	public function testThrowsExceptionWhenNotCallable() 
	{
		$this->adapter = new \Guzzle\Log\ClosureLogAdapter(123);
	}
}
?>