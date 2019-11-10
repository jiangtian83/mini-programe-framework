<?php  namespace Guzzle\Log;
class Zf1LogAdapter extends AbstractLogAdapter 
{
	public function __construct(\Zend_Log $logObject) 
	{
		$this->log = $logObject;
		\Guzzle\Common\Version::warn("Guzzle\\Log\\Zf1LogAdapter" . " is deprecated");
	}
	public function log($message, $priority = LOG_INFO, $extras = array( )) 
	{
		$this->log->log($message, $priority, $extras);
	}
}
?>