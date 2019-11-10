<?php  namespace Guzzle\Log;
class ClosureLogAdapter extends AbstractLogAdapter 
{
	public function __construct($logObject) 
	{
		if( !is_callable($logObject) ) 
		{
			throw new \InvalidArgumentException("Object must be callable");
		}
		$this->log = $logObject;
	}
	public function log($message, $priority = LOG_INFO, $extras = array( )) 
	{
		call_user_func($this->log, $message, $priority, $extras);
	}
}
?>