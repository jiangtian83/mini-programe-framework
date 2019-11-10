<?php  namespace Guzzle\Plugin\Backoff;
class BackoffLogger implements \Symfony\Component\EventDispatcher\EventSubscriberInterface 
{
	protected $logger = NULL;
	protected $formatter = NULL;
	const DEFAULT_FORMAT = "[{ts}] {method} {url} - {code} {phrase} - Retries: {retries}, Delay: {delay}, Time: {connect_time}, {total_time}, cURL: {curl_code} {curl_error}";
	public function __construct(\Guzzle\Log\LogAdapterInterface $logger, \Guzzle\Log\MessageFormatter $formatter = NULL) 
	{
		$this->logger = $logger;
		$this->formatter = ($formatter ?: new \Guzzle\Log\MessageFormatter(self::DEFAULT_FORMAT));
	}
	public static function getSubscribedEvents() 
	{
		return array( BackoffPlugin::RETRY_EVENT => "onRequestRetry" );
	}
	public function setTemplate($template) 
	{
		$this->formatter->setTemplate($template);
		return $this;
	}
	public function onRequestRetry(\Guzzle\Common\Event $event) 
	{
		$this->logger->log($this->formatter->format($event["request"], $event["response"], $event["handle"], array( "retries" => $event["retries"], "delay" => $event["delay"] )));
	}
}
?>