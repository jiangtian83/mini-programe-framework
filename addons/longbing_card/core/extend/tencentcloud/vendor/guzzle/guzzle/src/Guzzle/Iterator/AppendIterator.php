<?php  namespace Guzzle\Iterator;
class AppendIterator extends \AppendIterator 
{
	public function append(\Iterator $iterator) 
	{
		$this->getArrayIterator()->append($iterator);
	}
}
?>