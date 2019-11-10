<?php  namespace Guzzle\Service\Command;
class ClosureCommand extends AbstractCommand 
{
	protected function init() 
	{
		if( !$this["closure"] ) 
		{
			throw new \Guzzle\Common\Exception\InvalidArgumentException("A closure must be passed in the parameters array");
		}
	}
	protected function build() 
	{
		$closure = $this["closure"];
		$this->request = $closure($this, $this->operation);
		if( !$this->request || !$this->request instanceof \Guzzle\Http\Message\RequestInterface ) 
		{
			throw new \Guzzle\Common\Exception\UnexpectedValueException("Closure command did not return a RequestInterface object");
		}
	}
}
?>