<?php  namespace Guzzle\Log;
abstract class AbstractLogAdapter implements LogAdapterInterface 
{
	protected $log = NULL;
	public function getLogObject() 
	{
		return $this->log;
	}
}
?>