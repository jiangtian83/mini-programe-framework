<?php  namespace Guzzle\Batch;
interface BatchDivisorInterface 
{
	public function createBatches(\SplQueue $queue);
}
?>