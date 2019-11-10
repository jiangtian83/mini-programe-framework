<?php  namespace Qiniu\Storage;
final class FormUploader 
{
	public static function put($upToken, $key, $data, $config, $params, $mime, $fname) 
	{
		$fields = array( "token" => $upToken );
		if( $key === null ) 
		{
			$fname = "nullkey";
		}
		else 
		{
			$fields["key"] = $key;
		}
		$fields["crc32"] = Qiniu\crc32_data($data);
		if( $params ) 
		{
			foreach( $params as $k => $v ) 
			{
				$fields[$k] = $v;
			}
		}
		list($accessKey, $bucket, $err) = Qiniu\explodeUpToken($upToken);
		if( $err != null ) 
		{
			return array( null, $err );
		}
		$upHost = $config->getUpHost($accessKey, $bucket);
		$response = \Qiniu\Http\Client::multipartPost($upHost, $fields, "file", $fname, $data, $mime);
		if( !$response->ok() ) 
		{
			return array( null, new \Qiniu\Http\Error($upHost, $response) );
		}
		return array( $response->json(), null );
	}
	public static function putFile($upToken, $key, $filePath, $config, $params, $mime) 
	{
		$fields = array( "token" => $upToken, "file" => self::createFile($filePath, $mime) );
		if( $key !== null ) 
		{
			$fields["key"] = $key;
		}
		$fields["crc32"] = Qiniu\crc32_file($filePath);
		if( $params ) 
		{
			foreach( $params as $k => $v ) 
			{
				$fields[$k] = $v;
			}
		}
		$fields["key"] = $key;
		$headers = array( "Content-Type" => "multipart/form-data" );
		list($accessKey, $bucket, $err) = Qiniu\explodeUpToken($upToken);
		if( $err != null ) 
		{
			return array( null, $err );
		}
		$upHost = $config->getUpHost($accessKey, $bucket);
		$response = \Qiniu\Http\Client::post($upHost, $fields, $headers);
		if( !$response->ok() ) 
		{
			return array( null, new \Qiniu\Http\Error($upHost, $response) );
		}
		return array( $response->json(), null );
	}
	private static function createFile($filename, $mime) 
	{
		if( function_exists("curl_file_create") ) 
		{
			return curl_file_create($filename, $mime);
		}
		$value = "@" . $filename;
		if( !empty($mime) ) 
		{
			$value .= ";type=" . $mime;
		}
		return $value;
	}
}
?>