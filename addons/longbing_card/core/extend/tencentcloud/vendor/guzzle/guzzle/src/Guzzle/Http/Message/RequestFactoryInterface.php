<?php  namespace Guzzle\Http\Message;
interface RequestFactoryInterface 
{
	const OPTIONS_NONE = 0;
	const OPTIONS_AS_DEFAULTS = 1;
	public function fromMessage($message);
	public function fromParts($method, array $urlParts, $headers, $body, $protocol, $protocolVersion);
	public function create($method, $url, $headers, $body, array $options);
	public function applyOptions(RequestInterface $request, array $options, $flags);
}
?>