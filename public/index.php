<?php
require_once __DIR__ . '/../autoloader.php';

use BotDetection\BotDetector;
use BotDetection\ZipBomber;

$detector = new BotDetector();

if ($detector->isSuspicious()) {
	$bomber = new ZipBomber();
	$bomber->deliver();
}

echo 'Welcome to the site.';
