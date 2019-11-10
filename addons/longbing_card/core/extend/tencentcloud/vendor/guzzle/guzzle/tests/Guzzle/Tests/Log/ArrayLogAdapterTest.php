<?php  namespace Guzzle\Tests\Log;
class ArrayLogAdapterTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testLog() 
	{
		$adapter = new \Guzzle\Log\ArrayLogAdapter();
		$adapter->log("test", LOG_NOTICE, "127.0.0.1");
		$this->assertEquals(array( array( "message" => "test", "priority" => LOG_NOTICE, "extras" => "127.0.0.1" ) ), $adapter->getLogs());
	}
	public function testClearLog() 
	{
		$adapter = new \Guzzle\Log\ArrayLogAdapter();
		$adapter->log("test", LOG_NOTICE, "127.0.0.1");
		$adapter->clearLogs();
		$this->assertEquals(array( ), $adapter->getLogs());
	}
}
?>