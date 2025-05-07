<?php
require_once __DIR__ . '/../autoloader.php';

use BotDetection\BotDetector;
use BotDetection\ZipBomber;
use BotDetection\ThrottleGuard;
use BotDetection\Storage\RedisStorage;
use BotDetection\Storage\JsonStorage;
use BotDetection\Storage\StorageInterface;

$storageStrategy = null;
$redisHost = 'trafficguardian-redis';
$redisPort = 6379;
$redisConnectionTimeout = 0.5;

if (class_exists('Redis')) 
{
    $redis = new Redis();
    try 
    {
        if ($redis->connect($redisHost, $redisPort, $redisConnectionTimeout)) 
        { // 0.5 second timeout
            $storageStrategy = new RedisStorage($redis);
            error_log("TrafficGuardian: Using Redis for rate limiting.");
        } 
        else 
        {
            error_log("TrafficGuardian Warning: Failed to connect to Redis (host: $redisHost). Falling back to file storage.");
        }
    } 
    catch (RedisException $e) 
    {
        error_log("TrafficGuardian Warning: RedisException - " . $e->getMessage() . ". Falling back to file storage.");
    }
} 
else 
{
    error_log("TrafficGuardian Warning: Redis class not found. Falling back to file storage.");
}

if ($storageStrategy === null) 
{
    $storageStrategy = new jsonStorage(); // Uses default log path root/Logs/
    error_log("TrafficGuardian: Using FileStorage for rate limiting.");
}


$throttler = new ThrottleGuard($storageStrategy); 
$detector = new BotDetector($throttler);

if ($detector->isSuspicious()) {
    error_log("Suspicious request detected and blocked for IP: " . $throttler->getClientIP());
    $bomber = new ZipBomber();
    $bomber->deliver(); // This will exit
}

exit('Welcome!');
