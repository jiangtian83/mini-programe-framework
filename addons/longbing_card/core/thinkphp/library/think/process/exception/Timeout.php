<?php  namespace think\process\exception;
class Timeout extends \RuntimeException 
{
	private $process = NULL;
	private $timeoutType = NULL;
	const TYPE_GENERAL = 1;
	const TYPE_IDLE = 2;
	public function __construct(\think\Process $process, $timeoutType) 
	{
		$this->process = $process;
		$this->timeoutType = $timeoutType;
		parent::__construct(sprintf("The process \"%s\" exceeded the timeout of %s seconds.", $process->getCommandLine(), $this->getExceededTimeout()));
	}
	public function getProcess() 
	{
		return $this->process;
	}
	public function isGeneralTimeout() 
	{
		return $this->timeoutType === self::TYPE_GENERAL;
	}
	public function isIdleTimeout() 
	{
		return $this->timeoutType === self::TYPE_IDLE;
	}
	public function getExceededTimeout() 
	{
		switch( $this->timeoutType ) 
		{
			case self::TYPE_GENERAL: return $this->process->getTimeout();
			case self::TYPE_IDLE: return $this->process->getIdleTimeout();
			default: throw new \LogicException(sprintf("Unknown timeout type \"%d\".", $this->timeoutType));
		}
	}
}
?>