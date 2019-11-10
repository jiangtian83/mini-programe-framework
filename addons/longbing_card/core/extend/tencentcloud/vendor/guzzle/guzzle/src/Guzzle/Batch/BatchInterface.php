<?php  namespace Guzzle\Batch;
interface BatchInterface 
{
	public function add($item);
	public function flush();
	public function isEmpty();
}
?>