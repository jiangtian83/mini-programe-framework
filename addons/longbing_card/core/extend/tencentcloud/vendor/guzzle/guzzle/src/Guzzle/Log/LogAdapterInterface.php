<?php  namespace Guzzle\Log;
interface LogAdapterInterface 
{
	public function log($message, $priority, $extras);
}
?>