<?php  namespace Guzzle\Plugin\Md5;
class CommandContentMd5Plugin implements \Symfony\Component\EventDispatcher\EventSubscriberInterface 
{
	protected $contentMd5Param = NULL;
	protected $validateMd5Param = NULL;
	public function __construct($contentMd5Param = "ContentMD5", $validateMd5Param = "ValidateMD5") 
	{
		$this->contentMd5Param = $contentMd5Param;
		$this->validateMd5Param = $validateMd5Param;
	}
	public static function getSubscribedEvents() 
	{
		return array( "command.before_send" => array( "onCommandBeforeSend", -255 ) );
	}
	public function onCommandBeforeSend(\Guzzle\Common\Event $event) 
	{
		$command = $event["command"];
		$request = $command->getRequest();
		if( $request instanceof \Guzzle\Http\Message\EntityEnclosingRequestInterface && $request->getBody() && $command->getOperation()->hasParam($this->contentMd5Param) && $command[$this->contentMd5Param] === true && false !== ($md5 = $request->getBody()->getContentMd5(true, true)) ) 
		{
			$request->setHeader("Content-MD5", $md5);
		}
		if( $command[$this->validateMd5Param] === true ) 
		{
			$request->addSubscriber(new Md5ValidatorPlugin(true, false));
		}
	}
}
?>