<?php  namespace Guzzle\Stream;
interface StreamRequestFactoryInterface 
{
	public function fromRequest(\Guzzle\Http\Message\RequestInterface $request, $context, array $params);
}
?>