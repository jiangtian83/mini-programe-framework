<?php  namespace Guzzle\Iterator;
class ChunkedIterator extends \IteratorIterator 
{
	protected $chunkSize = NULL;
	protected $chunk = NULL;
	public function __construct(\Traversable $iterator, $chunkSize) 
	{
		$chunkSize = (int) $chunkSize;
		if( $chunkSize < 0 ) 
		{
			throw new \InvalidArgumentException("The chunk size must be equal or greater than zero; " . $chunkSize . " given");
		}
		parent::__construct($iterator);
		$this->chunkSize = $chunkSize;
	}
	public function rewind() 
	{
		parent::rewind();
		$this->next();
	}
	public function next() 
	{
		$this->chunk = array( );
		for( $i = 0; $i < $this->chunkSize && parent::valid();
		$i++ ) 
		{
			$this->chunk[] = parent::current();
			parent::next();
		}
	}
	public function current() 
	{
		return $this->chunk;
	}
	public function valid() 
	{
		return (bool) $this->chunk;
	}
}
?>