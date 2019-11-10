<?php  namespace Qcloud\Cos;
class Md5Listener implements \Symfony\Component\EventDispatcher\EventSubscriberInterface 
{
	private $signature = NULL;
	public static function getSubscribedEvents() 
	{
		return array( "command.after_prepare" => "onCommandAfterPrepare" );
	}
	public function __construct(Signature $signature) 
	{
		$this->signature = $signature;
	}
	public function onCommandAfterPrepare(\Guzzle\Common\Event $event) 
	{
		$command = $event["command"];
		$operation = $command->getOperation();
		if( $operation->getData("contentMd5") ) 
		{
			$this->addMd5($command);
		}
		else 
		{
			if( $operation->hasParam("ContentMD5") ) 
			{
				$value = $command["ContentMD5"];
				if( $value === true || $value === null && !$this->signature instanceof SignatureV4 ) 
				{
					$this->addMd5($command);
				}
			}
		}
	}
	private function addMd5(\Guzzle\Service\Command\CommandInterface $command) 
	{
		$request = $command->getRequest();
		$body = $request->getBody();
		if( $body && 0 < $body->getSize() && false !== ($md5 = $body->getContentMd5(true, true)) ) 
		{
			$request->setHeader("Content-MD5", $md5);
		}
	}
}
?>