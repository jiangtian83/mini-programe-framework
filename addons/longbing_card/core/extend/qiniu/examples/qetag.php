<?php  require_once(__DIR__ . "/../autoload.php");
$localFile = "/Users/jemy/Documents/qiniu.mp4";
list($etag, $err) = Qiniu\Etag::sum($localFile);
if( $err == NULL ) 
{
	echo "Etag: " . $etag;
}
else 
{
	var_dump($err);
}
?>