<?php  namespace Qcloud\Cos;
class Signature 
{
	private $accessKey = NULL;
	private $secretKey = NULL;
	public function __construct($accessKey, $secretKey) 
	{
		$this->accessKey = $accessKey;
		$this->secretKey = $secretKey;
	}
	public function __destruct() 
	{
	}
	public function signRequest(\Guzzle\Http\Message\RequestInterface $request) 
	{
		$signTime = (string) (time() - 60) . ";" . (string) (time() + 3600);
		$httpString = strtolower($request->getMethod()) . "\n" . urldecode($request->getPath()) . "\n\nhost=" . $request->getHost() . "\n";
		$sha1edHttpString = sha1($httpString);
		$stringToSign = "sha1\n" . $signTime . "\n" . $sha1edHttpString . "\n";
		$signKey = hash_hmac("sha1", $signTime, $this->secretKey);
		$signature = hash_hmac("sha1", $stringToSign, $signKey);
		$authorization = "q-sign-algorithm=sha1&q-ak=" . $this->accessKey . "&q-sign-time=" . $signTime . "&q-key-time=" . $signTime . "&q-header-list=host&q-url-param-list=&" . "q-signature=" . $signature;
		$request->setHeader("Authorization", $authorization);
	}
	public function createAuthorization(\Guzzle\Http\Message\RequestInterface $request, $expires = "10 minutes") 
	{
		$signTime = (string) (time() - 60) . ";" . (string) strtotime($expires);
		$httpString = strtolower($request->getMethod()) . "\n" . urldecode($request->getPath()) . "\n\nhost=" . $request->getHost() . "\n";
		$sha1edHttpString = sha1($httpString);
		$stringToSign = "sha1\n" . $signTime . "\n" . $sha1edHttpString . "\n";
		$signKey = hash_hmac("sha1", $signTime, $this->secretKey);
		$signature = hash_hmac("sha1", $stringToSign, $signKey);
		$authorization = "q-sign-algorithm=sha1&q-ak=" . $this->accessKey . "&q-sign-time=" . $signTime . "&q-key-time=" . $signTime . "&q-header-list=host&q-url-param-list=&" . "q-signature=" . $signature;
		return $authorization;
	}
	public function createPresignedUrl(\Guzzle\Http\Message\RequestInterface $request, $expires = "10 minutes") 
	{
		$authorization = $this->createAuthorization($request, $expires);
		$request->getQuery()->add("sign", $authorization);
		return $request->getUrl();
	}
}
?>