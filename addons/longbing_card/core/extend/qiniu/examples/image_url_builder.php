<?php  require_once(__DIR__ . "/../autoload.php");
$imageUrlBuilder = new Qiniu\Processing\ImageUrlBuilder();
$url = "http://78re52.com1.z0.glb.clouddn.com/resource/gogopher.jpg";
$url2 = "http://78re52.com1.z0.glb.clouddn.com/resource/gogopher.jpg?watermark/1/gravity/SouthEast/dx/0/dy/0/image/" . "aHR0cDovL2Fkcy1jZG4uY2h1Y2h1amllLmNvbS9Ga1R6bnpIY2RLdmRBUFc5cHZZZ3pTc21UY0tB";
$waterImage = "http://developer.qiniu.com/resource/logo-2.jpg";
$thumbLink = $imageUrlBuilder->thumbnail($url, 1, 100, 100);
$thumbLink2 = Qiniu\thumbnail($url2, 1, 100, 100);
var_dump($thumbLink, $thumbLink2);
$waterLink = $imageUrlBuilder->waterImg($url, $waterImage);
var_dump($waterLink);
$textLink = $imageUrlBuilder->waterText($url, "你瞅啥", "微软雅黑", 300);
var_dump($textLink);
?>