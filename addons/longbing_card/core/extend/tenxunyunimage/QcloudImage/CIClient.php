<?php  namespace QcloudImage;
class CIClient 
{
	private $bucket = NULL;
	private $auth = NULL;
	private $http = NULL;
	private $conf = NULL;
	public function __construct($appid, $secretId, $secretKey, $bucket) 
	{
		$this->bucket = $bucket;
		$this->auth = new Auth($appid, $secretId, $secretKey);
		$this->http = new HttpClient();
		$this->conf = new Conf();
	}
	public function useHttp() 
	{
		$this->conf->useHttp();
	}
	public function useHttps() 
	{
		$this->conf->useHttps();
	}
	public function setTimeout($timeout) 
	{
		$this->conf->setTimeout($timeout);
	}
	public function useNewDomain() 
	{
		$this->conf->useNewDomain();
	}
	public function useOldDomain() 
	{
		$this->conf->useOldDomain();
	}
	public function setProxy($proxy) 
	{
		$this->http->setProxy($proxy);
	}
	public function pornDetect($picture) 
	{
		if( !$picture || !is_array($picture) ) 
		{
			return Error::json(Error::$Param, "param picture must be array");
		}
		$reqUrl = $this->conf->buildUrl("/detection/pornDetect");
		$headers = $this->baseHeaders();
		$files = $this->baseParams();
		if( isset($picture["urls"]) ) 
		{
			$headers[] = "Content-Type:application/json";
			$files["url_list"] = $picture["urls"];
			$data = json_encode($files);
		}
		else 
		{
			if( isset($picture["files"]) ) 
			{
				$index = 0;
				foreach( $picture["files"] as $file ) 
				{
					if( PATH_SEPARATOR == ";" ) 
					{
						$path = iconv("UTF-8", "gb2312//IGNORE", $file);
					}
					else 
					{
						$path = $file;
					}
					$path = realpath($path);
					if( !file_exists($path) ) 
					{
						return Error::json(Error::$FilePath, "file " . $file . " not exist");
					}
					if( function_exists("curl_file_create") ) 
					{
						$files["image[" . $index . "]"] = curl_file_create($path);
					}
					else 
					{
						$files["image[" . $index . "]"] = "@" . $path;
					}
					$index++;
				}
				$data = $files;
			}
			else 
			{
				return Error::json(Error::$Param, "param picture is illegal");
			}
		}
		return $this->doRequest(array( "url" => $reqUrl, "method" => "POST", "data" => $data, "header" => $headers, "timeout" => $this->conf->timeout() ));
	}
	public function tagDetect($picture) 
	{
		if( !$picture || !is_array($picture) ) 
		{
			return Error::json(Error::$Param, "param picture must be array");
		}
		$reqUrl = $this->conf->buildUrl("/v1/detection/imagetag_detect");
		$headers = $this->baseHeaders();
		$headers[] = "Content-Type:application/json";
		$files = $this->baseParams();
		if( isset($picture["url"]) ) 
		{
			$files["url"] = $picture["url"];
		}
		else 
		{
			if( isset($picture["file"]) ) 
			{
				if( PATH_SEPARATOR == ";" ) 
				{
					$path = iconv("UTF-8", "gb2312//IGNORE", $picture["file"]);
				}
				else 
				{
					$path = $picture["file"];
				}
				$filePath = realpath($path);
				if( !file_exists($filePath) ) 
				{
					return Error::json(Error::$FilePath, "file " . $picture["file"] . " not exist");
				}
				$files["image"] = base64_encode(file_get_contents($filePath));
			}
			else 
			{
				if( isset($picture["buffer"]) ) 
				{
					$files["image"] = base64_encode($picture["buffer"]);
				}
				else 
				{
					return Error::json(Error::$Param, "param picture is illegal");
				}
			}
		}
		$data = json_encode($files);
		return $this->doRequest(array( "url" => $reqUrl, "method" => "POST", "data" => $data, "header" => $headers, "timeout" => $this->conf->timeout() ));
	}
	public function idcardDetect($picture, $cardType = 0) 
	{
		if( !$picture || !is_array($picture) ) 
		{
			return Error::json(Error::$Param, "param picture must be array");
		}
		if( $cardType !== 0 && $cardType !== 1 ) 
		{
			return Error::json(Error::$Param, "param cardType error");
		}
		$reqUrl = $this->conf->buildUrl("/ocr/idcard");
		$headers = $this->baseHeaders();
		$files = $this->baseParams();
		$files["card_type"] = $cardType;
		if( isset($picture["urls"]) ) 
		{
			$headers[] = "Content-Type:application/json";
			$files["url_list"] = $picture["urls"];
			$data = json_encode($files);
		}
		else 
		{
			if( isset($picture["files"]) ) 
			{
				$index = 0;
				foreach( $picture["files"] as $file ) 
				{
					if( PATH_SEPARATOR == ";" ) 
					{
						$path = iconv("UTF-8", "gb2312//IGNORE", $file);
					}
					else 
					{
						$path = $file;
					}
					$path = realpath($path);
					if( !file_exists($path) ) 
					{
						return Error::json(Error::$FilePath, "file " . $file . " not exist");
					}
					if( function_exists("curl_file_create") ) 
					{
						$files["image[" . $index . "]"] = curl_file_create($path);
					}
					else 
					{
						$files["image[" . $index . "]"] = "@" . $path;
					}
					$index++;
				}
				$data = $files;
			}
			else 
			{
				if( isset($picture["buffers"]) ) 
				{
					$index = 0;
					foreach( $picture["buffers"] as $buffer ) 
					{
						$files["image[" . $index . "]"] = $buffer;
						$index++;
					}
					$data = $files;
				}
				else 
				{
					return Error::json(Error::$Param, "param picture is illegal");
				}
			}
		}
		return $this->doRequest(array( "url" => $reqUrl, "method" => "POST", "data" => $data, "header" => $headers, "timeout" => $this->conf->timeout() ));
	}
	public function namecardV2Detect($picture) 
	{
		if( !$picture || !is_array($picture) ) 
		{
			return Error::json(Error::$Param, "param picture must be array");
		}
		$reqUrl = $this->conf->buildUrl("/ocr/businesscard");
		$headers = $this->baseHeaders();
		$files = $this->baseParams();
		if( isset($picture["urls"]) ) 
		{
			$headers[] = "Content-Type:application/json";
			$files["url_list"] = $picture["urls"];
			$data = json_encode($files);
		}
		else 
		{
			if( isset($picture["files"]) ) 
			{
				$index = 0;
				foreach( $picture["files"] as $file ) 
				{
					if( PATH_SEPARATOR == ";" ) 
					{
						$path = iconv("UTF-8", "gb2312//IGNORE", $file);
					}
					else 
					{
						$path = $file;
					}
					$path = realpath($path);
					if( !file_exists($path) ) 
					{
						return Error::json(Error::$FilePath, "file " . $file . " not exist");
					}
					if( function_exists("curl_file_create") ) 
					{
						$files["image[" . $index . "]"] = curl_file_create($path);
					}
					else 
					{
						$files["image[" . $index . "]"] = "@" . $path;
					}
					$index++;
				}
				$data = $files;
			}
			else 
			{
				if( isset($picture["buffers"]) ) 
				{
					$index = 0;
					foreach( $picture["buffers"] as $buffer ) 
					{
						$files["image[" . $index . "]"] = $buffer;
						$index++;
					}
					$data = $files;
				}
				else 
				{
					return Error::json(Error::$Param, "param picture is illegal");
				}
			}
		}
		return $this->doRequest(array( "url" => $reqUrl, "method" => "POST", "data" => $data, "header" => $headers, "timeout" => $this->conf->timeout() ));
	}
	public function drivingLicence($picture, $type = 0) 
	{
		if( !is_array($picture) ) 
		{
			return Error::json(Error::$Param, "param picture must be array");
		}
		if( $type !== 0 && $type !== 1 ) 
		{
			return Error::json(Error::$Param, "param type error");
		}
		$reqUrl = $this->conf->buildUrl("/ocr/drivinglicence");
		$headers = $this->baseHeaders();
		$files = $this->baseParams();
		if( isset($picture["url"]) ) 
		{
			$headers[] = "Content-Type:application/json";
			$files["url"] = $picture["url"];
			$files["type"] = $type;
			$data = json_encode($files);
		}
		else 
		{
			$files["type"] = strval($type);
			if( isset($picture["file"]) ) 
			{
				if( PATH_SEPARATOR == ";" ) 
				{
					$path = iconv("UTF-8", "gb2312//IGNORE", $picture["file"]);
				}
				else 
				{
					$path = $picture["file"];
				}
				$filePath = realpath($path);
				if( !file_exists($filePath) ) 
				{
					return Error::json(Error::$FilePath, "file " . $picture["file"] . " not exist");
				}
				if( function_exists("curl_file_create") ) 
				{
					$files["image"] = curl_file_create($filePath);
				}
				else 
				{
					$files["image"] = "@" . $filePath;
				}
			}
			else 
			{
				if( isset($picture["buffer"]) ) 
				{
					$files["image"] = $picture["buffer"];
				}
				else 
				{
					return Error::json(Error::$Param, "param picture is illegal");
				}
			}
			$data = $files;
		}
		return $this->doRequest(array( "url" => $reqUrl, "method" => "POST", "data" => $data, "header" => $headers, "timeout" => $this->conf->timeout() ));
	}
	public function plate($picture) 
	{
		if( !is_array($picture) ) 
		{
			return Error::json(Error::$Param, "param picture must be array");
		}
		$reqUrl = $this->conf->buildUrl("/ocr/plate");
		$headers = $this->baseHeaders();
		$files = $this->baseParams();
		if( isset($picture["url"]) ) 
		{
			$headers[] = "Content-Type:application/json";
			$files["url"] = $picture["url"];
			$data = json_encode($files);
		}
		else 
		{
			if( isset($picture["file"]) ) 
			{
				if( PATH_SEPARATOR == ";" ) 
				{
					$path = iconv("UTF-8", "gb2312//IGNORE", $picture["file"]);
				}
				else 
				{
					$path = $picture["file"];
				}
				$filePath = realpath($path);
				if( !file_exists($filePath) ) 
				{
					return Error::json(Error::$FilePath, "file " . $picture["file"] . " not exist");
				}
				if( function_exists("curl_file_create") ) 
				{
					$files["image"] = curl_file_create($filePath);
				}
				else 
				{
					$files["image"] = "@" . $filePath;
				}
			}
			else 
			{
				if( isset($picture["buffer"]) ) 
				{
					$files["image"] = $picture["buffer"];
				}
				else 
				{
					return Error::json(Error::$Param, "param picture is illegal");
				}
			}
			$data = $files;
		}
		return $this->doRequest(array( "url" => $reqUrl, "method" => "POST", "data" => $data, "header" => $headers, "timeout" => $this->conf->timeout() ));
	}
	public function bankcard($picture) 
	{
		if( !is_array($picture) ) 
		{
			return Error::json(Error::$Param, "param picture must be array");
		}
		$reqUrl = $this->conf->buildUrl("/ocr/bankcard");
		$headers = $this->baseHeaders();
		$files = $this->baseParams();
		if( isset($picture["url"]) ) 
		{
			$headers[] = "Content-Type:application/json";
			$files["url"] = $picture["url"];
			$data = json_encode($files);
		}
		else 
		{
			if( isset($picture["file"]) ) 
			{
				if( PATH_SEPARATOR == ";" ) 
				{
					$path = iconv("UTF-8", "gb2312//IGNORE", $picture["file"]);
				}
				else 
				{
					$path = $picture["file"];
				}
				$filePath = realpath($path);
				if( !file_exists($filePath) ) 
				{
					return Error::json(Error::$FilePath, "file " . $picture["file"] . " not exist");
				}
				if( function_exists("curl_file_create") ) 
				{
					$files["image"] = curl_file_create($filePath);
				}
				else 
				{
					$files["image"] = "@" . $filePath;
				}
			}
			else 
			{
				if( isset($picture["buffer"]) ) 
				{
					$files["image"] = $picture["buffer"];
				}
				else 
				{
					return Error::json(Error::$Param, "param picture is illegal");
				}
			}
			$data = $files;
		}
		return $this->doRequest(array( "url" => $reqUrl, "method" => "POST", "data" => $data, "header" => $headers, "timeout" => $this->conf->timeout() ));
	}
	public function bizlicense($picture) 
	{
		if( !is_array($picture) ) 
		{
			return Error::json(Error::$Param, "param picture must be array");
		}
		$reqUrl = $this->conf->buildUrl("/ocr/bizlicense");
		$headers = $this->baseHeaders();
		$files = $this->baseParams();
		if( isset($picture["url"]) ) 
		{
			$headers[] = "Content-Type:application/json";
			$files["url"] = $picture["url"];
			$data = json_encode($files);
		}
		else 
		{
			if( isset($picture["file"]) ) 
			{
				if( PATH_SEPARATOR == ";" ) 
				{
					$path = iconv("UTF-8", "gb2312//IGNORE", $picture["file"]);
				}
				else 
				{
					$path = $picture["file"];
				}
				$filePath = realpath($path);
				if( !file_exists($filePath) ) 
				{
					return Error::json(Error::$FilePath, "file " . $picture["file"] . " not exist");
				}
				if( function_exists("curl_file_create") ) 
				{
					$files["image"] = curl_file_create($filePath);
				}
				else 
				{
					$files["image"] = "@" . $filePath;
				}
			}
			else 
			{
				if( isset($picture["buffer"]) ) 
				{
					$files["image"] = $picture["buffer"];
				}
				else 
				{
					return Error::json(Error::$Param, "param picture is illegal");
				}
			}
			$data = $files;
		}
		return $this->doRequest(array( "url" => $reqUrl, "method" => "POST", "data" => $data, "header" => $headers, "timeout" => $this->conf->timeout() ));
	}
	public function general($picture) 
	{
		if( !is_array($picture) ) 
		{
			return Error::json(Error::$Param, "param picture must be array");
		}
		$reqUrl = $this->conf->buildUrl("/ocr/general");
		$headers = $this->baseHeaders();
		$files = $this->baseParams();
		if( isset($picture["url"]) ) 
		{
			$headers[] = "Content-Type:application/json";
			$files["url"] = $picture["url"];
			$data = json_encode($files);
		}
		else 
		{
			if( isset($picture["file"]) ) 
			{
				if( PATH_SEPARATOR == ";" ) 
				{
					$path = iconv("UTF-8", "gb2312//IGNORE", $picture["file"]);
				}
				else 
				{
					$path = $picture["file"];
				}
				$filePath = realpath($path);
				if( !file_exists($filePath) ) 
				{
					return Error::json(Error::$FilePath, "file " . $picture["file"] . " not exist");
				}
				if( function_exists("curl_file_create") ) 
				{
					$files["image"] = curl_file_create($filePath);
				}
				else 
				{
					$files["image"] = "@" . $filePath;
				}
			}
			else 
			{
				if( isset($picture["buffer"]) ) 
				{
					$files["image"] = $picture["buffer"];
				}
				else 
				{
					return Error::json(Error::$Param, "param picture is illegal");
				}
			}
			$data = $files;
		}
		return $this->doRequest(array( "url" => $reqUrl, "method" => "POST", "data" => $data, "header" => $headers, "timeout" => $this->conf->timeout() ));
	}
	public function handwriting($picture) 
	{
		if( !is_array($picture) ) 
		{
			return Error::json(Error::$Param, "param picture must be array");
		}
		$reqUrl = $this->conf->buildUrl("/ocr/handwriting");
		$headers = $this->baseHeaders();
		$files = $this->baseParams();
		if( isset($picture["url"]) ) 
		{
			$headers[] = "Content-Type:application/json";
			$files["url"] = $picture["url"];
			$data = json_encode($files);
		}
		else 
		{
			if( isset($picture["file"]) ) 
			{
				if( PATH_SEPARATOR == ";" ) 
				{
					$path = iconv("UTF-8", "gb2312//IGNORE", $picture["file"]);
				}
				else 
				{
					$path = $picture["file"];
				}
				$filePath = realpath($path);
				if( !file_exists($filePath) ) 
				{
					return Error::json(Error::$FilePath, "file " . $picture["file"] . " not exist");
				}
				if( function_exists("curl_file_create") ) 
				{
					$files["image"] = curl_file_create($filePath);
				}
				else 
				{
					$files["image"] = "@" . $filePath;
				}
			}
			else 
			{
				if( isset($picture["buffer"]) ) 
				{
					$files["image"] = $picture["buffer"];
				}
				else 
				{
					return Error::json(Error::$Param, "param picture is illegal");
				}
			}
			$data = $files;
		}
		return $this->doRequest(array( "url" => $reqUrl, "method" => "POST", "data" => $data, "header" => $headers, "timeout" => $this->conf->timeout() ));
	}
	public function faceNewPerson($personId, $groupIds, $picture, $personName = NULL, $tag = NULL) 
	{
		if( !is_array($groupIds) ) 
		{
			return Error::json(Error::$Param, "param groupIds must be array");
		}
		if( !is_array($picture) ) 
		{
			return Error::json(Error::$Param, "param picture must be array");
		}
		$reqUrl = $this->conf->buildUrl("/face/newperson");
		$headers = $this->baseHeaders();
		$files = $this->baseParams();
		$files["person_id"] = strval($personId);
		if( $personName ) 
		{
			$files["person_name"] = strval($personName);
		}
		if( $tag ) 
		{
			$files["tag"] = strval($tag);
		}
		if( isset($picture["url"]) ) 
		{
			$headers[] = "Content-Type:application/json";
			$files["group_ids"] = $groupIds;
			$files["url"] = $picture["url"];
			$data = json_encode($files);
		}
		else 
		{
			$index = 0;
			foreach( $groupIds as $groupId ) 
			{
				$files["group_ids[" . strval($index++) . "]"] = strval($groupId);
			}
			if( isset($picture["file"]) ) 
			{
				if( PATH_SEPARATOR == ";" ) 
				{
					$path = iconv("UTF-8", "gb2312//IGNORE", $picture["file"]);
				}
				else 
				{
					$path = $picture["file"];
				}
				$filePath = realpath($path);
				if( !file_exists($filePath) ) 
				{
					return Error::json(Error::$FilePath, "file " . $picture["file"] . " not exist");
				}
				if( function_exists("curl_file_create") ) 
				{
					$files["image"] = curl_file_create($filePath);
				}
				else 
				{
					$files["image"] = "@" . $filePath;
				}
			}
			else 
			{
				if( isset($picture["buffer"]) ) 
				{
					$files["image"] = $picture["buffer"];
				}
				else 
				{
					return Error::json(Error::$Param, "param picture is illegal");
				}
			}
			$data = $files;
		}
		return $this->doRequest(array( "url" => $reqUrl, "method" => "POST", "data" => $data, "header" => $headers, "timeout" => $this->conf->timeout() ));
	}
	public function faceDelPerson($personId) 
	{
		$reqUrl = $this->conf->buildUrl("/face/delperson");
		$headers = $this->baseHeaders();
		$headers[] = "Content-Type:application/json";
		$files = $this->baseParams();
		$files["person_id"] = strval($personId);
		return $this->doRequest(array( "url" => $reqUrl, "method" => "POST", "data" => json_encode($files), "header" => $headers, "timeout" => $this->conf->timeout() ));
	}
	public function faceAddFace($personId, $pictures, $tag = NULL) 
	{
		if( !is_array($pictures) ) 
		{
			return Error::json(Error::$Param, "param picture must be array");
		}
		$reqUrl = $this->conf->buildUrl("/face/addface");
		$headers = $this->baseHeaders();
		$files = $this->baseParams();
		$files["person_id"] = strval($personId);
		if( $tag ) 
		{
			$files["tag"] = strval($tag);
		}
		if( isset($pictures["urls"]) && is_array($pictures["urls"]) ) 
		{
			$headers[] = "Content-Type:application/json";
			$files["urls"] = $pictures["urls"];
			$data = json_encode($files);
		}
		else 
		{
			if( isset($pictures["files"]) && is_array($pictures["files"]) ) 
			{
				$index = 0;
				foreach( $pictures["files"] as $picture ) 
				{
					if( PATH_SEPARATOR == ";" ) 
					{
						$path = iconv("UTF-8", "gb2312//IGNORE", $picture);
					}
					else 
					{
						$path = $picture;
					}
					$filePath = realpath($path);
					if( !file_exists($filePath) ) 
					{
						return Error::json(Error::$FilePath, "file " . $picture . " not exist");
					}
					if( function_exists("curl_file_create") ) 
					{
						$files["images[" . $index . "]"] = curl_file_create($filePath);
					}
					else 
					{
						$files["images[" . $index . "]"] = "@" . $filePath;
					}
					$index++;
				}
			}
			else 
			{
				if( isset($pictures["buffers"]) && is_array($pictures["buffers"]) ) 
				{
					$index = 0;
					foreach( $pictures["buffers"] as $buffer ) 
					{
						$files["images[" . $index++ . "]"] = $buffer;
					}
				}
				else 
				{
					return Error::json(Error::$Param, "param pictures is illegal");
				}
			}
			$data = $files;
		}
		return $this->doRequest(array( "url" => $reqUrl, "method" => "POST", "data" => $data, "header" => $headers, "timeout" => $this->conf->timeout() ));
	}
	public function faceDelFace($personId, $faceIds) 
	{
		if( !is_array($faceIds) ) 
		{
			return Error::json(Error::$Param, "param faceIds must be array");
		}
		$reqUrl = $this->conf->buildUrl("/face/delface");
		$headers = $this->baseHeaders();
		$headers[] = "Content-Type:application/json";
		$files = $this->baseParams();
		$files["person_id"] = strval($personId);
		$files["face_ids"] = $faceIds;
		return $this->doRequest(array( "url" => $reqUrl, "method" => "POST", "data" => json_encode($files), "header" => $headers, "timeout" => $this->conf->timeout() ));
	}
	public function faceSetInfo($personId, $personName = NULL, $tag = NULL) 
	{
		$reqUrl = $this->conf->buildUrl("/face/setinfo");
		$headers = $this->baseHeaders();
		$headers[] = "Content-Type:application/json";
		$files = $this->baseParams();
		$files["person_id"] = strval($personId);
		if( $personName ) 
		{
			$files["person_name"] = strval($personName);
		}
		if( $tag ) 
		{
			$files["tag"] = strval($tag);
		}
		return $this->doRequest(array( "url" => $reqUrl, "method" => "POST", "data" => json_encode($files), "header" => $headers, "timeout" => $this->conf->timeout() ));
	}
	public function faceGetInfo($personId) 
	{
		$reqUrl = $this->conf->buildUrl("/face/getinfo");
		$headers = $this->baseHeaders();
		$headers[] = "Content-Type:application/json";
		$files = $this->baseParams();
		$files["person_id"] = strval($personId);
		return $this->doRequest(array( "url" => $reqUrl, "method" => "POST", "data" => json_encode($files), "header" => $headers, "timeout" => $this->conf->timeout() ));
	}
	public function faceGetGroupIds() 
	{
		$reqUrl = $this->conf->buildUrl("/face/getgroupids");
		$headers = $this->baseHeaders();
		$headers[] = "Content-Type:application/json";
		$files = $this->baseParams();
		return $this->doRequest(array( "url" => $reqUrl, "method" => "POST", "data" => json_encode($files), "header" => $headers, "timeout" => $this->conf->timeout() ));
	}
	public function faceGetPersonIds($groupId) 
	{
		$reqUrl = $this->conf->buildUrl("/face/getpersonids");
		$headers = $this->baseHeaders();
		$headers[] = "Content-Type:application/json";
		$files = $this->baseParams();
		$files["group_id"] = strval($groupId);
		return $this->doRequest(array( "url" => $reqUrl, "method" => "POST", "data" => json_encode($files), "header" => $headers, "timeout" => $this->conf->timeout() ));
	}
	public function faceGetFaceIds($personId) 
	{
		$reqUrl = $this->conf->buildUrl("/face/getfaceids");
		$headers = $this->baseHeaders();
		$headers[] = "Content-Type:application/json";
		$files = $this->baseParams();
		$files["person_id"] = strval($personId);
		return $this->doRequest(array( "url" => $reqUrl, "method" => "POST", "data" => json_encode($files), "header" => $headers, "timeout" => $this->conf->timeout() ));
	}
	public function faceGetFaceInfo($faceId) 
	{
		$reqUrl = $this->conf->buildUrl("/face/getfaceinfo");
		$headers = $this->baseHeaders();
		$headers[] = "Content-Type:application/json";
		$files = $this->baseParams();
		$files["face_id"] = strval($faceId);
		return $this->doRequest(array( "url" => $reqUrl, "method" => "POST", "data" => json_encode($files), "header" => $headers, "timeout" => $this->conf->timeout() ));
	}
	public function faceIdentify($groupId, $picture) 
	{
		if( !is_array($picture) ) 
		{
			return Error::json(Error::$Param, "param picture must be array");
		}
		$reqUrl = $this->conf->buildUrl("/face/identify");
		$headers = $this->baseHeaders();
		$files = $this->baseParams();
		$files["group_id"] = strval($groupId);
		if( isset($picture["url"]) ) 
		{
			$headers[] = "Content-Type:application/json";
			$files["url"] = $picture["url"];
			$data = json_encode($files);
		}
		else 
		{
			if( isset($picture["file"]) ) 
			{
				if( PATH_SEPARATOR == ";" ) 
				{
					$path = iconv("UTF-8", "gb2312//IGNORE", $picture["file"]);
				}
				else 
				{
					$path = $picture["file"];
				}
				$filePath = realpath($path);
				if( !file_exists($filePath) ) 
				{
					return Error::json(Error::$FilePath, "file " . $picture["file"] . " not exist");
				}
				if( function_exists("curl_file_create") ) 
				{
					$files["image"] = curl_file_create($filePath);
				}
				else 
				{
					$files["image"] = "@" . $filePath;
				}
			}
			else 
			{
				if( isset($picture["buffer"]) ) 
				{
					$files["image"] = $picture["buffer"];
				}
				else 
				{
					return Error::json(Error::$Param, "param picture is illegal");
				}
			}
			$data = $files;
		}
		return $this->doRequest(array( "url" => $reqUrl, "method" => "POST", "data" => $data, "header" => $headers, "timeout" => $this->conf->timeout() ));
	}
	public function faceVerify($personId, $picture) 
	{
		if( !is_array($picture) ) 
		{
			return Error::json(Error::$Param, "param picture must be array");
		}
		$reqUrl = $this->conf->buildUrl("/face/verify");
		$headers = $this->baseHeaders();
		$files = $this->baseParams();
		$files["person_id"] = strval($personId);
		if( isset($picture["url"]) ) 
		{
			$headers[] = "Content-Type:application/json";
			$files["url"] = $picture["url"];
			$data = json_encode($files);
		}
		else 
		{
			if( isset($picture["file"]) ) 
			{
				if( PATH_SEPARATOR == ";" ) 
				{
					$path = iconv("UTF-8", "gb2312//IGNORE", $picture["file"]);
				}
				else 
				{
					$path = $picture["file"];
				}
				$filePath = realpath($path);
				if( !file_exists($filePath) ) 
				{
					return Error::json(Error::$FilePath, "file " . $picture["file"] . " not exist");
				}
				if( function_exists("curl_file_create") ) 
				{
					$files["image"] = curl_file_create($filePath);
				}
				else 
				{
					$files["image"] = "@" . $filePath;
				}
			}
			else 
			{
				if( isset($picture["buffer"]) ) 
				{
					$files["image"] = $picture["buffer"];
				}
				else 
				{
					return Error::json(Error::$Param, "param picture is illegal");
				}
			}
			$data = $files;
		}
		return $this->doRequest(array( "url" => $reqUrl, "method" => "POST", "data" => $data, "header" => $headers, "timeout" => $this->conf->timeout() ));
	}
	public function faceCompare($pictureA, $pictureB) 
	{
		if( !is_array($pictureA) ) 
		{
			return Error::json(Error::$Param, "param pictureA must be array");
		}
		if( !is_array($pictureB) ) 
		{
			return Error::json(Error::$Param, "param pictureB must be array");
		}
		$reqUrl = $this->conf->buildUrl("/face/compare");
		$headers = $this->baseHeaders();
		$files = $this->baseParams();
		if( isset($pictureA["url"]) ) 
		{
			$files["urlA"] = $pictureA["url"];
		}
		else 
		{
			if( isset($pictureA["file"]) ) 
			{
				if( PATH_SEPARATOR == ";" ) 
				{
					$path = iconv("UTF-8", "gb2312//IGNORE", $pictureA["file"]);
				}
				else 
				{
					$path = $pictureA["file"];
				}
				$filePath = realpath($path);
				if( !file_exists($filePath) ) 
				{
					return Error::json(Error::$FilePath, "file " . $pictureA["file"] . " not exist");
				}
				if( function_exists("curl_file_create") ) 
				{
					$files["imageA"] = curl_file_create($filePath);
				}
				else 
				{
					$files["imageA"] = "@" . $filePath;
				}
			}
			else 
			{
				if( isset($pictureA["buffer"]) ) 
				{
					$files["imageA"] = $pictureA["buffer"];
				}
				else 
				{
					return Error::json(Error::$Param, "param pictureA is illegal");
				}
			}
		}
		if( isset($pictureB["url"]) ) 
		{
			$files["urlB"] = $pictureB["url"];
		}
		else 
		{
			if( isset($pictureB["file"]) ) 
			{
				$filePath = realpath($pictureB["file"]);
				if( PATH_SEPARATOR == ";" ) 
				{
					$path = iconv("UTF-8", "gb2312//IGNORE", $pictureB["file"]);
				}
				else 
				{
					$path = $pictureB["file"];
				}
				$filePath = realpath($path);
				if( !file_exists($filePath) ) 
				{
					return Error::json(Error::$FilePath, "file " . $pictureB["file"] . " not exist");
				}
				if( function_exists("curl_file_create") ) 
				{
					$files["imageB"] = curl_file_create($filePath);
				}
				else 
				{
					$files["imageB"] = "@" . $filePath;
				}
			}
			else 
			{
				if( isset($pictureB["buffer"]) ) 
				{
					$files["imageB"] = $pictureB["buffer"];
				}
				else 
				{
					return Error::json(Error::$Param, "param pictureB is illegal");
				}
			}
		}
		if( isset($pictureA["url"]) && isset($pictureB["ur"]) ) 
		{
			$headers[] = "Content-Type:application/json";
			$data = json_encode($files);
		}
		else 
		{
			$data = $files;
		}
		return $this->doRequest(array( "url" => $reqUrl, "method" => "POST", "data" => $data, "header" => $headers, "timeout" => $this->conf->timeout() ));
	}
	public function faceDetect($picture, $mode = 0) 
	{
		if( !is_array($picture) ) 
		{
			return Error::json(Error::$Param, "param picture must be array");
		}
		if( $mode !== 0 && $mode !== 1 ) 
		{
			return Error::json(Error::$Param, "param mode error");
		}
		$reqUrl = $this->conf->buildUrl("/face/detect");
		$headers = $this->baseHeaders();
		$files = $this->baseParams();
		if( isset($picture["url"]) ) 
		{
			$headers[] = "Content-Type:application/json";
			$files["mode"] = $mode;
			$files["url"] = $picture["url"];
			$data = json_encode($files);
		}
		else 
		{
			$files["mode"] = strval($mode);
			if( isset($picture["file"]) ) 
			{
				if( PATH_SEPARATOR == ";" ) 
				{
					$path = iconv("UTF-8", "gb2312//IGNORE", $picture["file"]);
				}
				else 
				{
					$path = $picture["file"];
				}
				$filePath = realpath($path);
				if( !file_exists($filePath) ) 
				{
					return Error::json(Error::$FilePath, "file " . $picture["file"] . " not exist");
				}
				if( function_exists("curl_file_create") ) 
				{
					$files["image"] = curl_file_create($filePath);
				}
				else 
				{
					$files["image"] = "@" . $filePath;
				}
			}
			else 
			{
				if( isset($picture["buffer"]) ) 
				{
					$files["image"] = $picture["buffer"];
				}
				else 
				{
					return Error::json(Error::$Param, "param picture is illegal");
				}
			}
			$data = $files;
		}
		return $this->doRequest(array( "url" => $reqUrl, "method" => "POST", "data" => $data, "header" => $headers, "timeout" => $this->conf->timeout() ));
	}
	public function faceShape($picture, $mode = 0) 
	{
		if( !is_array($picture) ) 
		{
			return Error::json(Error::$Param, "param picture must be array");
		}
		if( $mode !== 0 && $mode !== 1 ) 
		{
			return Error::json(Error::$Param, "param mode error");
		}
		$reqUrl = $this->conf->buildUrl("/face/shape");
		$headers = $this->baseHeaders();
		$files = $this->baseParams();
		if( isset($picture["url"]) ) 
		{
			$headers[] = "Content-Type:application/json";
			$files["mode"] = $mode;
			$files["url"] = $picture["url"];
			$data = json_encode($files);
		}
		else 
		{
			$files["mode"] = strval($mode);
			if( isset($picture["file"]) ) 
			{
				if( PATH_SEPARATOR == ";" ) 
				{
					$path = iconv("UTF-8", "gb2312//IGNORE", $picture["file"]);
				}
				else 
				{
					$path = $picture["file"];
				}
				$filePath = realpath($path);
				if( !file_exists($filePath) ) 
				{
					return Error::json(Error::$FilePath, "file " . $picture["file"] . " not exist");
				}
				if( function_exists("curl_file_create") ) 
				{
					$files["image"] = curl_file_create($filePath);
				}
				else 
				{
					$files["image"] = "@" . $filePath;
				}
			}
			else 
			{
				if( isset($picture["buffer"]) ) 
				{
					$files["image"] = $picture["buffer"];
				}
				else 
				{
					return Error::json(Error::$Param, "param picture is illegal");
				}
			}
			$data = $files;
		}
		return $this->doRequest(array( "url" => $reqUrl, "method" => "POST", "data" => $data, "header" => $headers, "timeout" => $this->conf->timeout() ));
	}
	public function multidentify($picture, $idtype) 
	{
		if( !$picture || !is_array($picture) ) 
		{
			return Error::json(Error::$Param, "param picture must be array");
		}
		$reqUrl = $this->conf->buildUrl("/face/multidentify");
		$headers = $this->baseHeaders();
		$files = $this->baseParams();
		if( isset($picture["url"]) ) 
		{
			$headers[] = "Content-Type:application/json";
			$files["url"] = $picture["url"];
			if( isset($idtype["group_id"]) ) 
			{
				$files["group_id"] = $idtype["group_id"];
			}
			else 
			{
				if( isset($idtype["group_ids"]) ) 
				{
					$files["group_ids"] = $idtype["group_ids"];
				}
				else 
				{
					return Error::json(Error::$Param, "param idtype is illegal");
				}
			}
		}
		else 
		{
			if( isset($picture["file"]) ) 
			{
				$headers[] = "Content-Type:multipart/form-data";
				if( PATH_SEPARATOR == ";" ) 
				{
					$path = iconv("UTF-8", "gb2312//IGNORE", $picture["file"]);
				}
				else 
				{
					$path = $picture["file"];
				}
				$filePath = realpath($path);
				if( !file_exists($filePath) ) 
				{
					return Error::json(Error::$FilePath, "file " . $picture["file"] . " not exist");
				}
				if( function_exists("curl_file_create") ) 
				{
					$files["image"] = curl_file_create($filePath);
				}
				else 
				{
					$files["image"] = "@" . $filePath;
				}
				if( isset($idtype["group_id"]) ) 
				{
					$files["group_id"] = $idtype["group_id"];
				}
				else 
				{
					if( isset($idtype["group_ids"]) ) 
					{
						if( !isset($picture["url"]) ) 
						{
							$index = 0;
							foreach( $idtype["group_ids"] as $id ) 
							{
								$files["group_ids[" . $index . "]"] = $id;
								$index++;
							}
						}
					}
					else 
					{
						return Error::json(Error::$Param, "param idtype is illegal");
					}
				}
			}
			else 
			{
				if( isset($picture["buffer"]) ) 
				{
					$files["image"] = $picture["buffer"];
				}
				else 
				{
					return Error::json(Error::$Param, "param picture is illegal");
				}
			}
		}
		if( isset($picture["url"]) ) 
		{
			$data = json_encode($files);
		}
		else 
		{
			if( isset($picture["file"]) ) 
			{
				$data = $files;
			}
		}
		return $this->doRequest(array( "url" => $reqUrl, "method" => "POST", "data" => $data, "header" => $headers, "timeout" => $this->conf->timeout() ));
	}
	public function liveDetectPicture($picture, $sign) 
	{
		if( !$picture || !is_array($picture) ) 
		{
			return Error::json(Error::$Param, "param picture must be array");
		}
		$reqUrl = $this->conf->buildUrl("/face/livedetectpicture");
		$headers = $this->baseHeaders();
		$headers[] = "Content-Type:application/json";
		$files = $this->baseParams();
		if( isset($picture["url"]) ) 
		{
			$files["url"] = $picture["url"];
			$files["sign"] = $sign;
		}
		else 
		{
			if( isset($picture["file"]) ) 
			{
				$files["sign"] = $sign;
				if( PATH_SEPARATOR == ";" ) 
				{
					$path = iconv("UTF-8", "gb2312//IGNORE", $picture["file"]);
				}
				else 
				{
					$path = $picture["file"];
				}
				$filePath = realpath($path);
				if( !file_exists($filePath) ) 
				{
					return Error::json(Error::$FilePath, "file " . $picture["file"] . " not exist");
				}
				$files["image"] = base64_encode(file_get_contents($filePath));
			}
			else 
			{
				if( isset($picture["buffer"]) ) 
				{
					$files["image"] = base64_encode($picture["buffer"]);
				}
				else 
				{
					return Error::json(Error::$Param, "param picture is illegal");
				}
			}
		}
		$data = json_encode($files);
		return $this->doRequest(array( "url" => $reqUrl, "method" => "POST", "data" => $data, "header" => $headers, "timeout" => $this->conf->timeout() ));
	}
	public function faceIdCardCompare($idcardNumber, $idcardName, $picture) 
	{
		if( !is_array($picture) ) 
		{
			return Error::json(Error::$Param, "param picture must be array");
		}
		$reqUrl = $this->conf->buildUrl("/face/idcardcompare");
		$headers = $this->baseHeaders();
		$files = $this->baseParams();
		$files["idcard_number"] = strval($idcardNumber);
		$files["idcard_name"] = strval($idcardName);
		if( isset($picture["url"]) ) 
		{
			$headers[] = "Content-Type:application/json";
			$files["url"] = $picture["url"];
			$data = json_encode($files);
		}
		else 
		{
			if( isset($picture["file"]) ) 
			{
				if( PATH_SEPARATOR == ";" ) 
				{
					$path = iconv("UTF-8", "gb2312//IGNORE", $picture["file"]);
				}
				else 
				{
					$path = $picture["file"];
				}
				$filePath = realpath($path);
				if( !file_exists($filePath) ) 
				{
					return Error::json(Error::$FilePath, "file " . $picture["file"] . " not exist");
				}
				if( function_exists("curl_file_create") ) 
				{
					$files["image"] = curl_file_create($filePath);
				}
				else 
				{
					$files["image"] = "@" . $filePath;
				}
			}
			else 
			{
				if( isset($picture["buffer"]) ) 
				{
					$files["image"] = $picture["buffer"];
				}
				else 
				{
					return Error::json(Error::$Param, "param picture is illegal");
				}
			}
			$data = $files;
		}
		return $this->doRequest(array( "url" => $reqUrl, "method" => "POST", "data" => $data, "header" => $headers, "timeout" => $this->conf->timeout() ));
	}
	public function faceLiveGetFour($seq = NULL) 
	{
		$reqUrl = $this->conf->buildUrl("/face/livegetfour");
		$headers = $this->baseHeaders();
		$headers[] = "Content-Type:application/json";
		$files = $this->baseParams();
		if( $seq ) 
		{
			$files["seq"] = strval($seq);
		}
		return $this->doRequest(array( "url" => $reqUrl, "method" => "POST", "data" => json_encode($files), "header" => $headers, "timeout" => $this->conf->timeout() ));
	}
	public function faceLiveDetectFour($validate, $video, $compareFlag, $card = NULL, $seq = NULL) 
	{
		if( !is_array($video) ) 
		{
			return Error::json(Error::$Param, "param video must be array");
		}
		$reqUrl = $this->conf->buildUrl("/face/livedetectfour");
		$headers = $this->baseHeaders();
		$files = $this->baseParams();
		$files["validate_data"] = strval($validate);
		if( isset($video["file"]) ) 
		{
			if( PATH_SEPARATOR == ";" ) 
			{
				$path = iconv("UTF-8", "gb2312//IGNORE", $video["file"]);
			}
			else 
			{
				$path = $video["file"];
			}
			$filePath = realpath($path);
			if( !file_exists($filePath) ) 
			{
				return Error::json(Error::$FilePath, "file " . $video["file"] . " not exist");
			}
			if( function_exists("curl_file_create") ) 
			{
				$files["video"] = curl_file_create($filePath);
			}
			else 
			{
				$files["video"] = "@" . $filePath;
			}
		}
		else 
		{
			if( isset($video["buffer"]) ) 
			{
				$files["video"] = $video["buffer"];
			}
			else 
			{
				return Error::json(Error::$Param, "param video is illegal");
			}
		}
		if( $compareFlag ) 
		{
			if( !is_array($card) ) 
			{
				return Error::json(Error::$Param, "param card must be array");
			}
			if( isset($card["file"]) ) 
			{
				if( PATH_SEPARATOR == ";" ) 
				{
					$path = iconv("UTF-8", "gb2312//IGNORE", $card["file"]);
				}
				else 
				{
					$path = $card["file"];
				}
				$filePath = realpath($path);
				if( !file_exists($filePath) ) 
				{
					return Error::json(Error::$FilePath, "file " . $card["file"] . " not exist");
				}
				if( function_exists("curl_file_create") ) 
				{
					$files["card"] = curl_file_create($filePath);
				}
				else 
				{
					$files["card"] = "@" . $filePath;
				}
			}
			else 
			{
				if( isset($card["buffer"]) ) 
				{
					$files["card"] = $card["buffer"];
				}
				else 
				{
					return Error::json(Error::$Param, "param card is illegal");
				}
			}
			$files["compare_flag"] = "true";
		}
		else 
		{
			$files["compare_flag"] = "false";
		}
		if( $seq ) 
		{
			$files["seq"] = strval($seq);
		}
		return $this->doRequest(array( "url" => $reqUrl, "method" => "POST", "data" => $files, "header" => $headers, "timeout" => $this->conf->timeout() ));
	}
	public function faceIdCardLiveDetectFour($validate, $video, $idcardNumber, $idcardName, $seq = NULL) 
	{
		if( !is_array($video) ) 
		{
			return Error::json(Error::$Param, "param video must be array");
		}
		$reqUrl = $this->conf->buildUrl("/face/idcardlivedetectfour");
		$headers = $this->baseHeaders();
		$files = $this->baseParams();
		$files["validate_data"] = strval($validate);
		$files["idcard_number"] = strval($idcardNumber);
		$files["idcard_name"] = strval($idcardName);
		if( isset($video["file"]) ) 
		{
			if( PATH_SEPARATOR == ";" ) 
			{
				$path = iconv("UTF-8", "gb2312//IGNORE", $video["file"]);
			}
			else 
			{
				$path = $video["file"];
			}
			$filePath = realpath($path);
			if( !file_exists($filePath) ) 
			{
				return Error::json(Error::$FilePath, "file " . $video["file"] . " not exist");
			}
			if( function_exists("curl_file_create") ) 
			{
				$files["video"] = curl_file_create($filePath);
			}
			else 
			{
				$files["video"] = "@" . $filePath;
			}
		}
		else 
		{
			if( isset($video["buffer"]) ) 
			{
				$files["video"] = $video["buffer"];
			}
			else 
			{
				return Error::json(Error::$Param, "param video is illegal");
			}
		}
		if( $seq ) 
		{
			$files["seq"] = strval($seq);
		}
		return $this->doRequest(array( "url" => $reqUrl, "method" => "POST", "data" => $files, "header" => $headers, "timeout" => $this->conf->timeout() ));
	}
	private function doRequest($request) 
	{
		$result = $this->http->sendRequest($request);
		$json = json_decode($result, true);
		if( $json ) 
		{
			$json["http_code"] = $this->http->statusCode();
			return json_encode($json);
		}
		return Error::json(Error::$Network, "response is not json: " . $result, $this->http->statusCode());
	}
	private function baseHeaders() 
	{
		return array( "Authorization:" . $this->auth->getSign($this->bucket), "User-Agent:" . Conf::getUa($this->auth->getAppId()) );
	}
	private function baseParams() 
	{
		return array( "appid" => $this->auth->getAppId(), "bucket" => $this->bucket );
	}
}
?>