<?php  namespace Guzzle\Stream;
class Stream implements StreamInterface 
{
	protected $stream = NULL;
	protected $size = NULL;
	protected $cache = array( );
	protected $customData = array( );
	protected static $readWriteHash = array( "read" => array( "r" => true, "w+" => true, "r+" => true, "x+" => true, "c+" => true, "rb" => true, "w+b" => true, "r+b" => true, "x+b" => true, "c+b" => true, "rt" => true, "w+t" => true, "r+t" => true, "x+t" => true, "c+t" => true, "a+" => true ), "write" => array( "w" => true, "w+" => true, "rw" => true, "r+" => true, "x+" => true, "c+" => true, "wb" => true, "w+b" => true, "r+b" => true, "x+b" => true, "c+b" => true, "w+t" => true, "r+t" => true, "x+t" => true, "c+t" => true, "a" => true, "a+" => true ) );
	const STREAM_TYPE = "stream_type";
	const WRAPPER_TYPE = "wrapper_type";
	const IS_LOCAL = "is_local";
	const IS_READABLE = "is_readable";
	const IS_WRITABLE = "is_writable";
	const SEEKABLE = "seekable";
	public function __construct($stream, $size = NULL) 
	{
		$this->setStream($stream, $size);
	}
	public function __destruct() 
	{
		$this->close();
	}
	public function __toString() 
	{
		if( !$this->isReadable() || !$this->isSeekable() && $this->isConsumed() ) 
		{
			return "";
		}
		$originalPos = $this->ftell();
		$body = stream_get_contents($this->stream, -1, 0);
		$this->seek($originalPos);
		return $body;
	}
	public function close() 
	{
		if( is_resource($this->stream) ) 
		{
			fclose($this->stream);
		}
		$this->cache[self::IS_READABLE] = false;
		$this->cache[self::IS_WRITABLE] = false;
	}
	public static function getHash(StreamInterface $stream, $algo, $rawOutput = false) 
	{
		$pos = $stream->ftell();
		if( !$stream->seek(0) ) 
		{
			return false;
		}
		$ctx = hash_init($algo);
		while( !$stream->feof() ) 
		{
			hash_update($ctx, $stream->read(8192));
		}
		$out = hash_final($ctx, (bool) $rawOutput);
		$stream->seek($pos);
		return $out;
	}
	public function getMetaData($key = NULL) 
	{
		$meta = stream_get_meta_data($this->stream);
		return (!$key ? $meta : (array_key_exists($key, $meta) ? $meta[$key] : null));
	}
	public function getStream() 
	{
		return $this->stream;
	}
	public function setStream($stream, $size = NULL) 
	{
		if( !is_resource($stream) ) 
		{
			throw new \Guzzle\Common\Exception\InvalidArgumentException("Stream must be a resource");
		}
		$this->size = $size;
		$this->stream = $stream;
		$this->rebuildCache();
		return $this;
	}
	public function detachStream() 
	{
		$this->stream = null;
		return $this;
	}
	public function getWrapper() 
	{
		return $this->cache[self::WRAPPER_TYPE];
	}
	public function getWrapperData() 
	{
		return ($this->getMetaData("wrapper_data") ?: array( ));
	}
	public function getStreamType() 
	{
		return $this->cache[self::STREAM_TYPE];
	}
	public function getUri() 
	{
		return $this->cache["uri"];
	}
	public function getSize() 
	{
		if( $this->size !== null ) 
		{
			return $this->size;
		}
		clearstatcache(true, $this->cache["uri"]);
		$stats = fstat($this->stream);
		if( isset($stats["size"]) ) 
		{
			$this->size = $stats["size"];
			return $this->size;
		}
		if( $this->cache[self::IS_READABLE] && $this->cache[self::SEEKABLE] ) 
		{
			$pos = $this->ftell();
			$this->size = strlen((string) $this);
			$this->seek($pos);
			return $this->size;
		}
		return false;
	}
	public function isReadable() 
	{
		return $this->cache[self::IS_READABLE];
	}
	public function isRepeatable() 
	{
		return $this->cache[self::IS_READABLE] && $this->cache[self::SEEKABLE];
	}
	public function isWritable() 
	{
		return $this->cache[self::IS_WRITABLE];
	}
	public function isConsumed() 
	{
		return feof($this->stream);
	}
	public function feof() 
	{
		return $this->isConsumed();
	}
	public function isLocal() 
	{
		return $this->cache[self::IS_LOCAL];
	}
	public function isSeekable() 
	{
		return $this->cache[self::SEEKABLE];
	}
	public function setSize($size) 
	{
		$this->size = $size;
		return $this;
	}
	public function seek($offset, $whence = SEEK_SET) 
	{
		return ($this->cache[self::SEEKABLE] ? fseek($this->stream, $offset, $whence) === 0 : false);
	}
	public function read($length) 
	{
		return fread($this->stream, $length);
	}
	public function write($string) 
	{
		$this->size = null;
		return fwrite($this->stream, $string);
	}
	public function ftell() 
	{
		return ftell($this->stream);
	}
	public function rewind() 
	{
		return $this->seek(0);
	}
	public function readLine($maxLength = NULL) 
	{
		if( !$this->cache[self::IS_READABLE] ) 
		{
			return false;
		}
		return ($maxLength ? fgets($this->getStream(), $maxLength) : fgets($this->getStream()));
	}
	public function setCustomData($key, $value) 
	{
		$this->customData[$key] = $value;
		return $this;
	}
	public function getCustomData($key) 
	{
		return (isset($this->customData[$key]) ? $this->customData[$key] : null);
	}
	protected function rebuildCache() 
	{
		$this->cache = stream_get_meta_data($this->stream);
		$this->cache[self::IS_LOCAL] = stream_is_local($this->stream);
		$this->cache[self::IS_READABLE] = isset(self::$readWriteHash["read"][$this->cache["mode"]]);
		$this->cache[self::IS_WRITABLE] = isset(self::$readWriteHash["write"][$this->cache["mode"]]);
	}
}
?>