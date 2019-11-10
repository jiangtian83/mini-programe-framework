<?php  namespace Guzzle\Log;
class Zf2LogAdapter extends AbstractLogAdapter 
{
	public function __construct(\Zend\Log\Logger $logObject) 
	{
		$this->log = $logObject;
	}
	public function log($message, $priority = LOG_INFO, $extras = array( )) 
	{
		$this->log->log($priority, $message, $extras);
	}
}
?>