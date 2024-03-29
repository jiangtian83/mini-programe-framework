<?php  require_once(__DIR__ . "/Common.php");
$bucketName = Common::getBucketName();
$object = "example.jpg";
$ossClient = Common::getOssClient();
$download_file = "download.jpg";
if( is_null($ossClient) ) 
{
	exit( 1 );
}
$ossClient->uploadFile($bucketName, $object, "example.jpg");
$options = array( OSS\OssClient::OSS_FILE_DOWNLOAD => $download_file, OSS\OssClient::OSS_PROCESS => "image/resize,m_fixed,h_100,w_100" );
$ossClient->getObject($bucketName, $object, $options);
printImage("imageResize", $download_file);
$options = array( OSS\OssClient::OSS_FILE_DOWNLOAD => $download_file, OSS\OssClient::OSS_PROCESS => "image/crop,w_100,h_100,x_100,y_100,r_1" );
$ossClient->getObject($bucketName, $object, $options);
printImage("iamgeCrop", $download_file);
$options = array( OSS\OssClient::OSS_FILE_DOWNLOAD => $download_file, OSS\OssClient::OSS_PROCESS => "image/rotate,90" );
$ossClient->getObject($bucketName, $object, $options);
printImage("imageRotate", $download_file);
$options = array( OSS\OssClient::OSS_FILE_DOWNLOAD => $download_file, OSS\OssClient::OSS_PROCESS => "image/sharpen,100" );
$ossClient->getObject($bucketName, $object, $options);
printImage("imageSharpen", $download_file);
$options = array( OSS\OssClient::OSS_FILE_DOWNLOAD => $download_file, OSS\OssClient::OSS_PROCESS => "image/watermark,text_SGVsbG8g5Zu-54mH5pyN5YqhIQ" );
$ossClient->getObject($bucketName, $object, $options);
printImage("imageWatermark", $download_file);
$options = array( OSS\OssClient::OSS_FILE_DOWNLOAD => $download_file, OSS\OssClient::OSS_PROCESS => "image/format,png" );
$ossClient->getObject($bucketName, $object, $options);
printImage("imageFormat", $download_file);
$options = array( OSS\OssClient::OSS_FILE_DOWNLOAD => $download_file, OSS\OssClient::OSS_PROCESS => "image/info" );
$ossClient->getObject($bucketName, $object, $options);
printImage("imageInfo", $download_file);
$timeout = 3600;
$options = array( OSS\OssClient::OSS_PROCESS => "image/resize,m_lfit,h_100,w_100" );
$signedUrl = $ossClient->signUrl($bucketName, $object, $timeout, "GET", $options);
Common::println("rtmp url: \n" . $signedUrl);
$ossClient->deleteObject($bucketName, $object);
function printImage($func, $imageFile) 
{
	$array = getimagesize($imageFile);
	Common::println((string) $func . ", image width: " . $array[0]);
	Common::println((string) $func . ", image height: " . $array[1]);
	Common::println((string) $func . ", image type: " . (($array[2] === 2 ? "jpg" : "png")));
	Common::println((string) $func . ", image size: " . ceil(filesize($imageFile)));
}
?>