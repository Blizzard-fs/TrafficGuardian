<?php

namespace BotDetection\Storage;

use Redis;

class RedisStorage implements StorageInterface 
{
    private Redis $redis;
    private string $keyPrefix = 'trafficguardian:ip:';
    private int $keyExpirySeconds = 86400;

    public function __construct(Redis $redis, string $keyPrefix = 'trafficguardian:ip:', int $keyExpirySeconds = 86400)
    {
        $this->redis = $redis;
        $this->keyPrefix = $keyPrefix;
        $this->keyExpirySeconds = $keyExpirySeconds;
    }

    private function getRedisKey(string $identifier): string
    {
        return $this->keyPrefix . $identifier;
    }

    public function load(string $identifier): array
    {
        $key = $this->getRedisKey($identifier);
        $serializedData = $this->redis->get($key);

        if ($serializedData === false || $serializedData === null) 
        {
            return [];
        }

        $data = unserialize($serializedData);
        return is_array($data) ? $data : [];
    }

    public function save(string $identifier, array $data): void
    {
        $key = $this->getRedisKey($identifier);
        $serializedData = serialize($data);
        $this->redis->setex($key, $this->keyExpirySeconds, $serializedData);
    }
}
