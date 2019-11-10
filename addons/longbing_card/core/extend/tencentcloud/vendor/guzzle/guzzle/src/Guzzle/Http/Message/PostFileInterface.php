<?php  namespace Guzzle\Http\Message;
interface PostFileInterface 
{
	public function setFieldName($name);
	public function getFieldName();
	public function setFilename($path);
	public function setPostname($name);
	public function getFilename();
	public function getPostname();
	public function setContentType($type);
	public function getContentType();
	public function getCurlValue();
}
?>