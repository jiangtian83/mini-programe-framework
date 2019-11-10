<?php  namespace Qcloud\Cos;
class UploadBodyListener implements \Symfony\Component\EventDispatcher\EventSubscriberInterface 
{
	protected $commands = NULL;
	protected $bodyParameter = NULL;
	protected $sourceParameter = NULL;
	public function __construct(array $commands, $bodyParameter = "Body", $sourceParameter = "SourceFile") 
	{
		$this->commands = $commands;
		$this->bodyParameter = (string) $bodyParameter;
		$this->sourceParameter = (string) $sourceParameter;
	}
	public static function getSubscribedEvents() 
	{
		return array( "command.before_prepare" => array( "onCommandBeforePrepare" ) );
	}
	public function onCommandBeforePrepare(\Guzzle\Common\Event $event) 
	{
		$command = $event["command"];
		if( in_array($command->getName(), $this->commands) ) 
		{
			$source = $command->get($this->sourceParameter);
			$body = $command->get($this->bodyParameter);
			if( is_string($source) && file_exists($source) ) 
			{
				$body = fopen($source, "rb");
			}
			if( null !== $body ) 
			{
				$command->remove($this->sourceParameter);
				$command->set($this->bodyParameter, \Guzzle\Http\EntityBody::factory($body));
			}
			else 
			{
				throw new Exception\InvalidArgumentException("You must specify a non-null value for the " . $this->bodyParameter . " or " . $this->sourceParameter . " parameters.");
			}
		}
	}
}
?>