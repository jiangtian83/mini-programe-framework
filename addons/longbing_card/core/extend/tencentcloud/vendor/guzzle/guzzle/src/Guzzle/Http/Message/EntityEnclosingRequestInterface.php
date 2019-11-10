<?php  namespace Guzzle\Http\Message;
interface EntityEnclosingRequestInterface extends RequestInterface 
{
	const URL_ENCODED = "application/x-www-form-urlencoded; charset=utf-8";
	const MULTIPART = "multipart/form-data";
	public function setBody($body, $contentType);
	public function getBody();
	public function getPostField($field);
	public function getPostFields();
	public function setPostField($key, $value);
	public function addPostFields($fields);
	public function removePostField($field);
	public function getPostFiles();
	public function getPostFile($fieldName);
	public function removePostFile($fieldName);
	public function addPostFile($field, $filename, $contentType, $postname);
	public function addPostFiles(array $files);
	public function configureRedirects($strict, $maxRedirects);
}
?>