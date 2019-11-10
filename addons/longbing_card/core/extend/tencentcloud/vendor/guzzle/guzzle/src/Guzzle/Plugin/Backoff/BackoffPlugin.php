<?php  namespace Guzzle\Plugin\Backoff;
class BackoffPlugin extends \Guzzle\Common\AbstractHasDispatcher implements \Symfony\Component\EventDispatcher\EventSubscriberInterface 
{
	protected $strategy = NULL;
	const DELAY_PARAM = \Guzzle\Http\Curl\CurlMultiInterface::BLOCKING;
	const RETRY_PARAM = "plugins.backoff.retry_count";
	const RETRY_EVENT = "plugins.backoff.retry";
	public function __construct(BackoffStrategyInterface $strategy = NULL) 
	{
		$this->strategy = $strategy;
	}
	public static function getExponentialBackoff($maxRetries = 3, array $httpCodes = NULL, array $curlCodes = NULL) 
	{
		return new self(new TruncatedBackoffStrategy($maxRetries, new HttpBackoffStrategy($httpCodes, new CurlBackoffStrategy($curlCodes, new ExponentialBackoffStrategy()))));
	}
	public static function getAllEvents() 
	{
		return array( self::RETRY_EVENT );
	}
	public static function getSubscribedEvents() 
	{
		return array( "request.sent" => "onRequestSent", "request.exception" => "onRequestSent", \Guzzle\Http\Curl\CurlMultiInterface::POLLING_REQUEST => "onRequestPoll" );
	}
	public function onRequestSent(\Guzzle\Common\Event $event) 
	{
		$request = $event["request"];
		$response = $event["response"];
		$exception = $event["exception"];
		$params = $request->getParams();
		$retries = (int) $params->get(self::RETRY_PARAM);
		$delay = $this->strategy->getBackoffPeriod($retries, $request, $response, $exception);
		if( $delay !== false ) 
		{
			$params->set(self::RETRY_PARAM, ++$retries)->set(self::DELAY_PARAM, microtime(true) + $delay);
			$request->setState(\Guzzle\Http\Message\RequestInterface::STATE_TRANSFER);
			$this->dispatch(self::RETRY_EVENT, array( "request" => $request, "response" => $response, "handle" => ($exception && $exception instanceof \Guzzle\Http\Exception\CurlException ? $exception->getCurlHandle() : null), "retries" => $retries, "delay" => $delay ));
		}
	}
	public function onRequestPoll(\Guzzle\Common\Event $event) 
	{
		$request = $event["request"];
		$delay = $request->getParams()->get(self::DELAY_PARAM);
		if( null !== $delay && $delay <= microtime(true) ) 
		{
			$request->getParams()->remove(self::DELAY_PARAM);
			if( $request instanceof \Guzzle\Http\Message\EntityEnclosingRequestInterface && $request->getBody() ) 
			{
				$request->getBody()->seek(0);
			}
			$multi = $event["curl_multi"];
			$multi->remove($request);
			$multi->add($request);
		}
	}
}
?>