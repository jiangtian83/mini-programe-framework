<?php  namespace Guzzle\Tests\Batch;
class AbstractBatchDecoratorTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testProxiesToWrappedObject() 
	{
		$batch = new \Guzzle\Batch\Batch($this->getMock("Guzzle\\Batch\\BatchTransferInterface"), $this->getMock("Guzzle\\Batch\\BatchDivisorInterface"));
		$decoratorA = $this->getMockBuilder("Guzzle\\Batch\\AbstractBatchDecorator")->setConstructorArgs(array( $batch ))->getMockForAbstractClass();
		$decoratorB = $this->getMockBuilder("Guzzle\\Batch\\AbstractBatchDecorator")->setConstructorArgs(array( $decoratorA ))->getMockForAbstractClass();
		$decoratorA->add("foo");
		$this->assertFalse($decoratorB->isEmpty());
		$this->assertFalse($batch->isEmpty());
		$this->assertEquals(array( $decoratorB, $decoratorA ), $decoratorB->getDecorators());
		$this->assertEquals(array( ), $decoratorB->flush());
	}
}
?>