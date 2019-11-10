<?php  namespace Guzzle\Service\Exception;
class InconsistentClientTransferException extends \Guzzle\Common\Exception\RuntimeException 
{
	private $invalidCommands = array( );
	public function __construct(array $commands) 
	{
		$this->invalidCommands = $commands;
		parent::__construct("Encountered commands in a batch transfer that use inconsistent clients. The batching " . "strategy you use with a command transfer must divide command batches by client.");
	}
	public function getCommands() 
	{
		return $this->invalidCommands;
	}
}
?>