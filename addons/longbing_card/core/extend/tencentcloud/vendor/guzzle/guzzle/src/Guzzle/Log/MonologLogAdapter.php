<?php  namespace Guzzle\Log;
class MonologLogAdapter extends AbstractLogAdapter 
{
	private static $mapping = NULL;
	public function __construct(\Monolog\Logger $logObject) 
	{
		$this->log = $logObject;
	}
	public function log($message, $priority = LOG_INFO, $extras = array( )) 
	{
		$this->log->addRecord(self::$mapping[$priority], $message, $extras);
	}
}
?>