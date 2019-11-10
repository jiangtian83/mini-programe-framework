<?php  namespace Guzzle\Http;
interface EntityBodyInterface extends \Guzzle\Stream\StreamInterface 
{
	public function setRewindFunction($callable);
	public function compress($filter);
	public function uncompress($filter);
	public function getContentLength();
	public function getContentType();
	public function getContentMd5($rawOutput, $base64Encode);
	public function getContentEncoding();
}
?>