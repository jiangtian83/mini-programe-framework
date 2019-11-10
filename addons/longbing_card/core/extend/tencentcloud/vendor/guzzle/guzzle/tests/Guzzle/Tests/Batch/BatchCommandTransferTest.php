<?php  namespace Guzzle\Tests\Batch;
class BatchCommandTransferTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testCreatesBatchesBasedOnClient() 
	{
		$client1 = new \Guzzle\Service\Client("http://www.example.com");
		$client2 = new \Guzzle\Service\Client("http://www.example.com");
		$commands = array( new \Guzzle\Tests\Service\Mock\Command\MockCommand(), new \Guzzle\Tests\Service\Mock\Command\MockCommand(), new \Guzzle\Tests\Service\Mock\Command\MockCommand(), new \Guzzle\Tests\Service\Mock\Command\MockCommand(), new \Guzzle\Tests\Service\Mock\Command\MockCommand() );
		$queue = new \SplQueue();
		foreach( $commands as $i => $command ) 
		{
			if( $i % 2 ) 
			{
				$command->setClient($client1);
			}
			else 
			{
				$command->setClient($client2);
			}
			$queue[] = $command;
		}
		$batch = new \Guzzle\Batch\BatchCommandTransfer(2);
		$this->assertEquals(array( array( $commands[0], $commands[2] ), array( $commands[4] ), array( $commands[1], $commands[3] ) ), $batch->createBatches($queue));
	}
	public function testEnsuresAllItemsAreCommands() 
	{
		$queue = new \SplQueue();
		$queue[] = "foo";
		$batch = new \Guzzle\Batch\BatchCommandTransfer(2);
		$batch->createBatches($queue);
	}
	public function testTransfersBatches() 
	{
		$client = $this->getMockBuilder("Guzzle\\Service\\Client")->setMethods(array( "send" ))->getMock();
		$client->expects($this->once())->method("send");
		$command = new \Guzzle\Tests\Service\Mock\Command\MockCommand();
		$command->setClient($client);
		$batch = new \Guzzle\Batch\BatchCommandTransfer(2);
		$batch->transfer(array( $command ));
	}
	public function testDoesNotTransfersEmptyBatches() 
	{
		$batch = new \Guzzle\Batch\BatchCommandTransfer(2);
		$batch->transfer(array( ));
	}
	public function testEnsuresAllCommandsUseTheSameClient() 
	{
		$batch = new \Guzzle\Batch\BatchCommandTransfer(2);
		$client1 = new \Guzzle\Service\Client("http://www.example.com");
		$client2 = new \Guzzle\Service\Client("http://www.example.com");
		$command1 = new \Guzzle\Tests\Service\Mock\Command\MockCommand();
		$command1->setClient($client1);
		$command2 = new \Guzzle\Tests\Service\Mock\Command\MockCommand();
		$command2->setClient($client2);
		$batch->transfer(array( $command1, $command2 ));
	}
}
?>