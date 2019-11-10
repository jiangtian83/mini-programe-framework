<?php  namespace Guzzle\Http;
class RedirectPlugin implements \Symfony\Component\EventDispatcher\EventSubscriberInterface 
{
	protected $defaultMaxRedirects = 5;
	const REDIRECT_COUNT = "redirect.count";
	const MAX_REDIRECTS = "redirect.max";
	const STRICT_REDIRECTS = "redirect.strict";
	const PARENT_REQUEST = "redirect.parent_request";
	const DISABLE = "redirect.disable";
	public static function getSubscribedEvents() 
	{
		return array( "request.sent" => array( "onRequestSent", 100 ), "request.clone" => "cleanupRequest", "request.before_send" => "cleanupRequest" );
	}
	public function cleanupRequest(\Guzzle\Common\Event $event) 
	{
		$params = $event["request"]->getParams();
		unset($params[self::REDIRECT_COUNT]);
		unset($params[self::PARENT_REQUEST]);
	}
	public function onRequestSent(\Guzzle\Common\Event $event) 
	{
		$response = $event["response"];
		$request = $event["request"];
		if( !$response || $request->getParams()->get(self::DISABLE) ) 
		{
			return NULL;
		}
		$original = $this->getOriginalRequest($request);
		if( !$response->isRedirect() || !$response->hasHeader("Location") ) 
		{
			if( $request !== $original ) 
			{
				$response->getParams()->set(self::REDIRECT_COUNT, $original->getParams()->get(self::REDIRECT_COUNT));
				$original->setResponse($response);
				$response->setEffectiveUrl($request->getUrl());
			}
		}
		else 
		{
			$this->sendRedirectRequest($original, $request, $response);
		}
	}
	protected function getOriginalRequest(Message\RequestInterface $request) 
	{
		$original = $request;
		while( $parent = $original->getParams()->get(self::PARENT_REQUEST) ) 
		{
			$original = $parent;
		}
		return $original;
	}
	protected function createRedirectRequest(Message\RequestInterface $request, $statusCode, $location, Message\RequestInterface $original) 
	{
		$redirectRequest = null;
		$strict = $original->getParams()->get(self::STRICT_REDIRECTS);
		if( $request instanceof Message\EntityEnclosingRequestInterface && ($statusCode == 303 || !$strict && $statusCode <= 302) ) 
		{
			$redirectRequest = Message\RequestFactory::getInstance()->cloneRequestWithMethod($request, "GET");
		}
		else 
		{
			$redirectRequest = clone $request;
		}
		$redirectRequest->setIsRedirect(true);
		$redirectRequest->setResponseBody($request->getResponseBody());
		$location = Url::factory($location);
		if( !$location->isAbsolute() ) 
		{
			$originalUrl = $redirectRequest->getUrl(true);
			$originalUrl->getQuery()->clear();
			$location = $originalUrl->combine((string) $location, true);
		}
		$redirectRequest->setUrl($location);
		$redirectRequest->getEventDispatcher()->addListener("request.before_send", $func = function($e) use (&$func, $request, $redirectRequest) 
		{
			$redirectRequest->getEventDispatcher()->removeListener("request.before_send", $func);
			$e["request"]->getParams()->set(RedirectPlugin::PARENT_REQUEST, $request);
		}
		);
		if( $redirectRequest instanceof Message\EntityEnclosingRequestInterface && $redirectRequest->getBody() ) 
		{
			$body = $redirectRequest->getBody();
			if( $body->ftell() && !$body->rewind() ) 
			{
				throw new Exception\CouldNotRewindStreamException("Unable to rewind the non-seekable entity body of the request after redirecting. cURL probably " . "sent part of body before the redirect occurred. Try adding acustom rewind function using on the " . "entity body of the request using setRewindFunction().");
			}
		}
		return $redirectRequest;
	}
	protected function prepareRedirection(Message\RequestInterface $original, Message\RequestInterface $request, Message\Response $response) 
	{
		$params = $original->getParams();
		$current = $params[self::REDIRECT_COUNT] + 1;
		$params[self::REDIRECT_COUNT] = $current;
		$max = (isset($params[self::MAX_REDIRECTS]) ? $params[self::MAX_REDIRECTS] : $this->defaultMaxRedirects);
		if( $max < $current ) 
		{
			$this->throwTooManyRedirectsException($original, $max);
			return false;
		}
		return $this->createRedirectRequest($request, $response->getStatusCode(), trim($response->getLocation()), $original);
	}
	protected function sendRedirectRequest(Message\RequestInterface $original, Message\RequestInterface $request, Message\Response $response) 
	{
		if( $redirectRequest = $this->prepareRedirection($original, $request, $response) ) 
		{
			try 
			{
				$redirectRequest->send();
			}
			catch( Exception\BadResponseException $e ) 
			{
				$e->getResponse();
				if( !$e->getResponse() ) 
				{
					throw $e;
				}
			}
		}
	}
	protected function throwTooManyRedirectsException(Message\RequestInterface $original, $max) 
	{
		$original->getEventDispatcher()->addListener("request.complete", $func = function($e) use (&$func, $original, $max) 
		{
			$original->getEventDispatcher()->removeListener("request.complete", $func);
			$str = (string) $max . " redirects were issued for this request:\n" . $e["request"]->getRawHeaders();
			throw new Exception\TooManyRedirectsException($str);
		}
		);
	}
}
?>