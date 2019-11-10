<?php  namespace Guzzle\Common;
class Event extends \Symfony\Component\EventDispatcher\Event implements ToArrayInterface, \ArrayAccess, \IteratorAggregate 
{
	private $context = NULL;
	public function __construct(array $context = array( )) 
	{
		$this->context = $context;
	}
	public function getIterator() 
	{
		return new \ArrayIterator($this->context);
	}
	public function offsetGet($offset) 
	{
		return (isset($this->context[$offset]) ? $this->context[$offset] : null);
	}
	public function offsetSet($offset, $value) 
	{
		$this->context[$offset] = $value;
	}
	public function offsetExists($offset) 
	{
		return isset($this->context[$offset]);
	}
	public function offsetUnset($offset) 
	{
		unset($this->context[$offset]);
	}
	public function toArray() 
	{
		return $this->context;
	}
}
?>