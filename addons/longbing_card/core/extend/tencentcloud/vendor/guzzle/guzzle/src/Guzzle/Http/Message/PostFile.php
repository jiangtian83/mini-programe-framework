<?php  namespace Guzzle\Http\Message;
class PostFile implements PostFileInterface 
{
	protected $fieldName = NULL;
	protected $contentType = NULL;
	protected $filename = NULL;
	protected $postname = NULL;
	public function __construct($fieldName, $filename, $contentType = NULL, $postname = NULL) 
	{
		$this->fieldName = $fieldName;
		$this->setFilename($filename);
		$this->postname = ($postname ? $postname : basename($filename));
		$this->contentType = ($contentType ?: $this->guessContentType());
	}
	public function setFieldName($name) 
	{
		$this->fieldName = $name;
		return $this;
	}
	public function getFieldName() 
	{
		return $this->fieldName;
	}
	public function setFilename($filename) 
	{
		if( strpos($filename, "@") === 0 ) 
		{
			$filename = substr($filename, 1);
		}
		if( !is_readable($filename) ) 
		{
			throw new \Guzzle\Common\Exception\InvalidArgumentException("Unable to open " . $filename . " for reading");
		}
		$this->filename = $filename;
		return $this;
	}
	public function setPostname($postname) 
	{
		$this->postname = $postname;
		return $this;
	}
	public function getFilename() 
	{
		return $this->filename;
	}
	public function getPostname() 
	{
		return $this->postname;
	}
	public function setContentType($type) 
	{
		$this->contentType = $type;
		return $this;
	}
	public function getContentType() 
	{
		return $this->contentType;
	}
	public function getCurlValue() 
	{
		if( function_exists("curl_file_create") ) 
		{
			return curl_file_create($this->filename, $this->contentType, $this->postname);
		}
		$value = "@" . $this->filename . ";filename=" . $this->postname;
		if( $this->contentType ) 
		{
			$value .= ";type=" . $this->contentType;
		}
		return $value;
	}
	public function getCurlString() 
	{
		\Guzzle\Common\Version::warn("Guzzle\\Http\\Message\\PostFile::getCurlString" . " is deprecated. Use getCurlValue()");
		return $this->getCurlValue();
	}
	protected function guessContentType() 
	{
		return (\Guzzle\Http\Mimetypes::getInstance()->fromFilename($this->filename) ?: "application/octet-stream");
	}
}
?>