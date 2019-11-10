<?php  namespace Guzzle\Tests\Service\Exception;
class InconsistentClientTransferExceptionTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testStoresCommands() 
	{
		$items = array( "foo", "bar" );
		$e = new \Guzzle\Service\Exception\InconsistentClientTransferException($items);
		$this->assertEquals($items, $e->getCommands());
	}
}
?>