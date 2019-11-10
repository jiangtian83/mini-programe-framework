<?php  error_reporting(32767 | 2048);
require_once("PHPUnit/TextUI/TestRunner.php");
require(dirname(__DIR__) . "/vendor/autoload.php");
$servicesFile = __DIR__ . "/Guzzle/Tests/TestData/services/services.json";
$builder = Guzzle\Service\Builder\ServiceBuilder::factory($servicesFile);
Guzzle\Tests\GuzzleTestCase::setServiceBuilder($builder);
?>