<?php  namespace Guzzle\Http;
class ReadLimitEntityBody extends AbstractEntityBodyDecorator 
{
	protected $limit = NULL;
	protected $offset = NULL;
	public function __construct(EntityBodyInterface $body, $limit, $offset = 0) 
	{
		parent::__construct($body);
		$this->setLimit($limit)->setOffset($offset);
	}
	public function __toString() 
	{
		if( !$this->body->isReadable() || !$this->body->isSeekable() && $this->body->isConsumed() ) 
		{
			return "";
		}
		$originalPos = $this->body->ftell();
		$this->body->seek($this->offset);
		$data = "";
		while( !$this->feof() ) 
		{
			$data .= $this->read(1048576);
		}
		$this->body->seek($originalPos);
		return ((string) $data ?: "");
	}
	public function isConsumed() 
	{
		return $this->body->isConsumed() || $this->offset + $this->limit <= $this->body->ftell();
	}
	public function getContentLength() 
	{
		$length = $this->body->getContentLength();
		return ($length === false ? $this->limit : min($this->limit, min($length, $this->offset + $this->limit) - $this->offset));
	}
	public function seek($offset, $whence = SEEK_SET) 
	{
		return ($whence === SEEK_SET ? $this->body->seek(max($this->offset, min($this->offset + $this->limit, $offset))) : false);
	}
	public function setOffset($offset) 
	{
		$this->body->seek($offset);
		$this->offset = $offset;
		return $this;
	}
	public function setLimit($limit) 
	{
		$this->limit = $limit;
		return $this;
	}
	public function read($length) 
	{
		$remaining = ($this->offset + $this->limit) - $this->body->ftell();
		if( 0 < $remaining ) 
		{
			return $this->body->read(min($remaining, $length));
		}
		return false;
	}
}
?>