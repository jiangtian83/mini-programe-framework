<?php  namespace Guzzle\Log;
class ArrayLogAdapter implements LogAdapterInterface 
{
	protected $logs = array( );
	public function log($message, $priority = LOG_INFO, $extras = array( )) 
	{
		$this->logs[] = array( "message" => $message, "priority" => $priority, "extras" => $extras );
	}
	public function getLogs() 
	{
		return $this->logs;
	}
	public function clearLogs() 
	{
		$this->logs = array( );
	}
}
?>