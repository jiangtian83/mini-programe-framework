<?php  namespace Guzzle\Common\Exception;
class ExceptionCollection extends \Exception implements GuzzleException, \IteratorAggregate, \Countable 
{
	protected $exceptions = array( );
	private $shortMessage = NULL;
	public function __construct($message = "", $code = 0, \Exception $previous = NULL) 
	{
		parent::__construct($message, $code, $previous);
		$this->shortMessage = $message;
	}
	public function setExceptions(array $exceptions) 
	{
		$this->exceptions = array( );
		foreach( $exceptions as $exception ) 
		{
			$this->add($exception);
		}
		return $this;
	}
	public function add($e) 
	{
		$this->exceptions[] = $e;
		if( $this->message ) 
		{
			$this->message .= "\n";
		}
		$this->message .= $this->getExceptionMessage($e, 0);
		return $this;
	}
	public function count() 
	{
		return count($this->exceptions);
	}
	public function getIterator() 
	{
		return new \ArrayIterator($this->exceptions);
	}
	public function getFirst() 
	{
		return ($this->exceptions ? $this->exceptions[0] : null);
	}
	private function getExceptionMessage(\Exception $e, $depth = 0) 
	{
		static $sp = "    ";
		$prefix = ($depth ? str_repeat($sp, $depth) : "");
		$message = (string) $prefix . "(" . get_class($e) . ") " . $e->getFile() . " line " . $e->getLine() . "\n";
		if( $e instanceof $this ) 
		{
			if( $e->shortMessage ) 
			{
				$message .= "\n" . $prefix . $sp . str_replace("\n", "\n" . $prefix . $sp, $e->shortMessage) . "\n";
			}
			foreach( $e as $ee ) 
			{
				$message .= "\n" . $this->getExceptionMessage($ee, $depth + 1);
			}
		}
		else 
		{
			$message .= "\n" . $prefix . $sp . str_replace("\n", "\n" . $prefix . $sp, $e->getMessage()) . "\n";
			$message .= "\n" . $prefix . $sp . str_replace("\n", "\n" . $prefix . $sp, $e->getTraceAsString()) . "\n";
		}
		return str_replace(getcwd(), ".", $message);
	}
}
?>