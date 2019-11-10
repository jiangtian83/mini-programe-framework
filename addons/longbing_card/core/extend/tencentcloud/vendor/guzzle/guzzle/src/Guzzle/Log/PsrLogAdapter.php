<?php  namespace Guzzle\Log;
class PsrLogAdapter extends AbstractLogAdapter 
{
	private static $mapping = NULL;
	public function __construct(\Psr\Log\LoggerInterface $logObject) 
	{
		$this->log = $logObject;
	}
	public function log($message, $priority = LOG_INFO, $extras = array( )) 
	{
		$this->log->log(self::$mapping[$priority], $message, $extras);
	}
}
?>