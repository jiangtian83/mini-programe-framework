<?php  namespace app\common\controller;
class Upload 
{
	protected $request = NULL;
	protected $mini_config = NULL;
	protected $path_type = NULL;
	protected $attachment_model = NULL;
	public function __construct(\think\Request $request = NULL) 
	{
		if( is_null($request) ) 
		{
			$request = \think\Request::instance();
		}
		$this->request = $request;
		$this->attachment_model = new \app\base\model\BaseAttachment();
		$this->mini_config = new \app\base\model\BaseConfig();
	}
	public function upload($uniacid, $type, $filename = "") 
	{
		if( $type == "picture" ) 
		{
			$image_config = array( "mimes" => "image/*", "maxSize" => 2097152, "exts" => "gif,jpg,jpeg,bmp,png", "subName" => array( "date", "Y-m-d" ), "rootPath" => PUBLIC_PATH . "uploads/picture", "weiqinPath" => $_SERVER["DOCUMENT_ROOT"] . "/attachment/upload/" . $uniacid . "/picture", "savePath" => "", "saveName" => "uniqid", "driver" => "local" );
		}
		if( $type == "audio" ) 
		{
			$image_config = array( "maxSize" => 20971520099999, "exts" => "mp3,wma,wav,m4a", "subName" => array( "date", "Y-m-d" ), "rootPath" => PUBLIC_PATH . "uploads/audio", "weiqinPath" => $_SERVER["DOCUMENT_ROOT"] . "/attachment/upload/" . $uniacid . "/audio", "savePath" => "", "saveName" => "uniqid", "driver" => "local" );
		}
		if( $type == "video" ) 
		{
			$image_config = array( "maxSize" => 2097152, "exts" => "wmv,mp4,avi,mpg,rmvb", "subName" => array( "date", "Y-m-d" ), "rootPath" => PUBLIC_PATH . "uploads/video", "weiqinPath" => $_SERVER["DOCUMENT_ROOT"] . "/attachment/upload/" . $uniacid . "/video", "savePath" => "", "saveName" => "uniqid", "driver" => "local" );
		}
		if( $type == "cert" ) 
		{
			$image_config = array( "maxSize" => 2097152, "exts" => "pem", "filename" => $filename, "rootPath" => ROOT_PATH . "weixinpay/cert", "weiqinPath" => ROOT_PATH . "weixinpay/cert", "savePath" => "", "driver" => "local" );
		}
		$rootPath = $image_config["rootPath"];
		if( defined("IS_WEIQIN") ) 
		{
			$rootPath = $image_config["weiqinPath"];
		}
		if( isset($image_config["subName"]) && $image_config["subName"] ) 
		{
			$upload_path = $rootPath . "/" . call_user_func_array($image_config["subName"][0], array( $image_config["subName"][1], time() ));
		}
		else 
		{
			$upload_path = $rootPath;
		}
		$file = $this->request->file("file");
		if( $file->validate(array( "size" => $image_config["maxSize"], "ext" => $image_config["exts"] )) ) 
		{
			if( isset($image_config["saveName"]) && $image_config["saveName"] ) 
			{
				$info = $file->rule($image_config["saveName"])->move($upload_path, true, false);
			}
			else 
			{
				$info = $file->move($upload_path, $image_config["filename"], true);
			}
			if( $file->getError() ) 
			{
				return array( "code" => -1, "msg" => $file->getError(), "data" => array( ) );
			}
			if( $type == "cert" ) 
			{
				return $info;
			}
			$upload_info = $this->parseFile($info);
			$mini_config = $this->mini_config->get(array( "uniacid" => $uniacid ));
			if( $mini_config["open_oss"] == 1 ) 
			{
				$oss_res = $this->oss_upload($upload_info["path"], $uniacid);
				$upload_info["driver"] = "aliyun";
				if( isset($oss_res["info"]["url"]) ) 
				{
					$upload_info["src"] = str_replace("http://", "https://", $oss_res["info"]["url"]);
				}
				else 
				{
					$return = array( "code" => -1, "msg" => $oss_res, "data" => array( ) );
					return $return;
				}
			}
			else 
			{
				if( $mini_config["open_oss"] == 2 ) 
				{
					list($ret, $err) = $this->qiniu_upload($upload_info["path"], $uniacid);
					$upload_info["driver"] = "qiniuyun";
					if( $err !== null ) 
					{
						$upload_info["src"] = "";
					}
					else 
					{
						$upload_info["src"] = $mini_config["qiniu_yuming"] . "/" . $ret["key"];
					}
				}
				else 
				{
					if( $mini_config["open_oss"] == 3 ) 
					{
						$res = $this->tenxunyun_upload($upload_info["path"], $uniacid);
						$upload_info["driver"] = "tenxunyn";
						if( isset($res["ETag"]) && isset($res["ObjectURL"]) ) 
						{
							$upload_info["src"] = $mini_config["tenxunyun_yuming"] . "/longbing_cardcloud/" . $upload_info["path"];
						}
						else 
						{
							$upload_info["src"] = "";
						}
					}
					else 
					{
						$upload_info["driver"] = "local";
						if( defined("IS_WEIQIN") ) 
						{
							$media_path = "/";
						}
						else 
						{
							if( defined("MEDIA_PATH") ) 
							{
								$media_path = MEDIA_PATH;
							}
							else 
							{
								$media_path = "/";
							}
						}
						$upload_info["src"] = getHttpUrlRoot() . $media_path . $upload_info["path"];
					}
				}
			}
			$ori = $this->attachment_model->where(array( "uniacid" => $uniacid, "sha1" => $upload_info["sha1"], "driver" => $upload_info["driver"] ))->find();
			$upload_info["uniacid"] = $uniacid;
			$upload_info["status"] = 1;
			if( $ori ) 
			{
				$upload_info["id"] = $ori["id"];
				$return = array( "code" => 0, "msg" => "success", "data" => $upload_info );
			}
			else 
			{
				$res = $this->attachment_model->insertGetId($upload_info);
				if( $res ) 
				{
					$upload_info["id"] = $res;
					$return = array( "code" => 0, "msg" => "success", "data" => $upload_info );
				}
				else 
				{
					$return = array( "code" => -1, "msg" => "save_sql_fail", "data" => array( ) );
				}
			}
		}
		else 
		{
			$return = array( "code" => -1, "msg" => $file->getError(), "data" => array( ) );
		}
		if( $return["code"] == 0 ) 
		{
			$return["data"] = lb_image_rules($return["data"], array( "src" ), "list_cover_small", $uniacid);
		}
		return $return;
	}
	protected function parseFile($info) 
	{
		if( defined("IS_WEIQIN") ) 
		{
			$start = strlen($_SERVER["DOCUMENT_ROOT"]) + 1;
		}
		else 
		{
			$start = strlen(PUBLIC_PATH);
		}
		$data = array( );
		if( !empty($info) ) 
		{
			$data["create_time"] = $info->getATime();
			$data["ext"] = $info->getExtension();
			$data["alt"] = str_replace("." . $data["ext"], "", $info->getInfo()["name"]);
			$data["name"] = $data["alt"];
			$data["path_type"] = $this->path_type;
			$data["mime_type"] = ($info->getMime() ? strstr($info->getMime(), "/", true) : "");
			$data["path"] = str_replace("\\", "/", substr($info->getPathname(), $start));
			$data["size"] = $info->getSize();
			$data["md5"] = md5_file($info->getPathname());
			$data["sha1"] = sha1_file($info->getPathname());
		}
		return $data;
	}
	public function oss_upload($object = "", $uniacid = 0) 
	{
		$mini_config = $this->mini_config->get(array( "uniacid" => $uniacid ));
		$path = ltrim($object, "/");
		$object = $mini_config["base_dir"] . "/" . $object;
		try 
		{
			$bucket = $mini_config["bucket"];
			if( defined("IS_WEIQIN") ) 
			{
				$filePath = $_SERVER["DOCUMENT_ROOT"] . "/" . $path;
			}
			else 
			{
				$filePath = PUBLIC_PATH . $path;
			}
			if( file_exists($filePath) ) 
			{
				require_once(EXTEND_PATH . "aliyuncs/oss-sdk-php/autoload.php");
				$ossClient = new \OSS\OssClient($mini_config["access_key_id"], $mini_config["access_key_secret"], $mini_config["endpoint"]);
				$res = $ossClient->uploadFile($bucket, $object, $filePath);
				return $res;
			}
		}
		catch( \OSS\Core\OssException $e ) 
		{
			return $e->getMessage();
		}
		return true;
	}
	public function qiniu_upload($path = "", $uniacid = 0) 
	{
		require_once(ROOT_PATH . "extend/qiniu/autoload.php");
		$mini_config = $this->mini_config->get(array( "uniacid" => $uniacid ));
		$accessKey = $mini_config["accesskey"];
		$secretKey = $mini_config["secretkey"];
		$bucket = $mini_config["qiniu_bucket"];
		$auth = new \Qiniu\Auth($accessKey, $secretKey);
		$token = $auth->uploadToken($bucket);
		if( defined("IS_WEIQIN") ) 
		{
			$path = ltrim($path, "/");
			$filePath = $_SERVER["DOCUMENT_ROOT"] . "/" . $path;
		}
		else 
		{
			$filePath = PUBLIC_PATH . $path;
		}
		$key = $path;
		$uploadMgr = new \Qiniu\Storage\UploadManager();
		list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
		return array( $ret, $err );
	}
	public function tenxunyun_upload($path = "", $uniacid = 0) 
	{
		$path = ltrim($path, "/");
		require(EXTEND_PATH . "tencentcloud/vendor/autoload.php");
		$mini_config = $this->mini_config->get(array( "uniacid" => $uniacid ));
		$appid = $mini_config["tenxunyun_appid"];
		$secretid = $mini_config["tenxunyun_secretid"];
		$secretkey = $mini_config["tenxunyun_secretkey"];
		$bucket = $mini_config["tenxunyun_bucket"];
		$region = $mini_config["tenxunyun_region"];
		$yuming = $mini_config["tenxunyun_yuming"];
		$cosClient = new \Qcloud\Cos\Client(array( "region" => $region, "credentials" => array( "secretId" => $secretid, "secretKey" => $secretkey ) ));
		$key = "longbing_cardcloud/" . $path;
		if( defined("IS_WEIQIN") ) 
		{
			$body = $_SERVER["DOCUMENT_ROOT"] . "/" . $path;
		}
		else 
		{
			$body = PUBLIC_PATH . $path;
		}
		try 
		{
			$result = $cosClient->putObject(array( "Bucket" => $bucket, "Key" => $key, "Body" => file_get_contents($body) ));
			return $result;
		}
		catch( \Exception $e ) 
		{
			return $e;
		}
	}
}
?>