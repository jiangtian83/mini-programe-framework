<?php  namespace Guzzle\Iterator;
class MapIterator extends \IteratorIterator 
{
	protected $callback = NULL;
	public function __construct(\Traversable $iterator, $callback) 
	{
		parent::__construct($iterator);
		if( !is_callable($callback) ) 
		{
			throw new \Guzzle\Common\Exception\InvalidArgumentException("The callback must be callable");
		}
		$this->callback = $callback;
	}
	public function current() 
	{
		return call_user_func($this->callback, parent::current());
	}
}
?>