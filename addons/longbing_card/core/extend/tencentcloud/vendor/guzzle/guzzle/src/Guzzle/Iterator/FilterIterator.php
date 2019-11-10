<?php  namespace Guzzle\Iterator;
class FilterIterator extends \FilterIterator 
{
	protected $callback = NULL;
	public function __construct(\Iterator $iterator, $callback) 
	{
		parent::__construct($iterator);
		if( !is_callable($callback) ) 
		{
			throw new \Guzzle\Common\Exception\InvalidArgumentException("The callback must be callable");
		}
		$this->callback = $callback;
	}
	public function accept() 
	{
		return call_user_func($this->callback, $this->current());
	}
}
?>