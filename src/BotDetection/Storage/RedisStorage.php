<?php

namespace BotDetection\Storage;

use BotDetection\Models\ClientRequestData;
use Redis;
use RedisException;

class RedisStorage implements StorageInterface
{
    private Redis $redis;
    private string $keyPrefix;
    private int $keyExpirySeconds;

    /**
     * RedisStorage constructor.
     *
     * @param Redis $redis The Redis client instance.
     * @param string $keyPrefix Prefix for all keys stored in Redis.
     * @param int $keyExpirySeconds Default expiration time for keys in seconds.
     */
    public function __construct(Redis $redis, string $keyPrefix = 'trafficguardian:ip:', int $keyExpirySeconds = 86400)
    {
        $this->redis = $redis;
        $this->keyPrefix = $keyPrefix;
        $this->keyExpirySeconds = $keyExpirySeconds;
    }

    /**
     * Generates the Redis key for a given identifier.
     *
     * @param string $identifier The identifier (e.g., IP address).
     * @return string The Redis key.
     */
    private function getRedisKey(string $identifier): string
    {
        return $this->keyPrefix . $identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function load(string $identifier): ?ClientRequestData
    {
        $key = $this->getRedisKey($identifier);
        $serializedData = false;

        try 
        {
            $serializedData = $this->redis->get($key);
        } 
        catch (RedisException $e) 
        {
            error_log("RedisStorage Error: Failed to get data for key {$key}. Error: " . $e->getMessage());
            return null;
        }

        if ($serializedData === false || $serializedData === null) 
        {
            return null;
        }

        $dataObject = @unserialize($serializedData);

        if ($dataObject instanceof ClientRequestData) 
        {
            return $dataObject;
        } 
        elseif (is_array($dataObject)) 
        {
            error_log("RedisStorage Warning: Found old array data for key {$key}, attempting to convert to ClientRequestData object.");
            return ClientRequestData::fromArray($dataObject);
        }
        
        error_log("RedisStorage Error: Unserialized data is not a ClientRequestData object or a supported array for key: {$key}. Data: " . print_r($serializedData, true));

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function save(string $identifier, ClientRequestData $data): void
    {
        $key = $this->getRedisKey($identifier);
        try 
        {
            $serializedData = serialize($data);

            if ($serializedData === false) 
            {
                error_log("RedisStorage Error: Failed to serialize ClientRequestData object for key {$key}.");
                return;
            }

            $this->redis->setex($key, $this->keyExpirySeconds, $serializedData);

        } 
        catch (RedisException $e) 
        {
            error_log("RedisStorage Error: Failed to save data for key {$key}. Error: " . $e->getMessage());
        }
    }
}
