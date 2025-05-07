<?php

namespace BotDetection;

use BotDetection\Storage\StorageInterface;

class ThrottleGuard
{
    protected string $logPath = __DIR__ . '/../../Logs/';
    protected int $limitPerSecond = 10;
    protected int $timeoutSeconds = 10;
    protected int $maxViolations = 5;

    private StorageInterface $storage;
    /**
     * ThrottleGuard constructor.
     *
     * @param Redis $redis The Redis client instance.
     */
    public function __construct(StorageInterface $storage)
    {
        $this->setStorage($storage);
    }

    /**
     * get the StorageInterface.
     *
     * @return Redis
     */
    public function getStorage(): ?StorageInterface 
    {
        return $this->storage; 
    }

    /**
     * set the storageInterface.
     *
     * @param StorageInterface $storage The storage to save requests.
     */
    public function setStorage(StorageInterface $storage): void
    {
        $this->storage = $storage;
    }

    /**
     * Get the log path.
     *
     * @return string
     */
    public function getLogPath(): string
    {
        return $this->logPath;
    }

    /**
     * Set the log path.
     *
     * @param string $logPath
     */
    public function setLogPath(string $logPath): void
    {
        $this->logPath = $logPath;
    }

    /**
     * Get the limit of requests per second.
     *
     * @return int
     */
    public function getLimitPerSecond(): int
    {
        return $this->limitPerSecond;
    }

    /**
     * Set the limit of requests per second.
     *
     * @param int $limitPerSecond
     */
    public function setLimitPerSecond(int $limitPerSecond): void
    {
        $this->limitPerSecond = $limitPerSecond;
    }

    /**
     * Get the timeout duration in seconds.
     *
     * @return int
     */
    public function getTimeoutSeconds(): int
    {
        return $this->timeoutSeconds;
    }

    /**
     * Set the timeout duration in seconds.
     *
     * @param int $timeoutSeconds
     */
    public function setTimeoutSeconds(int $timeoutSeconds): void
    {
        $this->timeoutSeconds = $timeoutSeconds;
    }

    /**
     * Get the maximum number of violations before blocking the IP.
     *
     * @return int
     */
    public function getMaxViolations(): int
    {
        return $this->maxViolations;
    }

    /**
     * Set the maximum number of violations before blocking the IP.
     *
     * @param int $maxViolations
     */
    public function setMaxViolations(int $maxViolations): void
    {
        $this->maxViolations = $maxViolations;
    }

    /**
     * Determine if the current IP is blocked due to too many violations.
     *
     * @return bool
     */
    public function isBlocked(): bool
    {
        $clientIP = $this->getClientIP();
        $data = $this->storage->load($clientIP);

        if (!isset($data['violations'])) return false;

        return $data['violations'] >= $this->getMaxViolations();
    }

    /**
     * Check if the rate limit has been exceeded.
     * @return bool
     */
    public function isRateLimitExceeded(): bool
    {
        $clientIP = $this->getClientIP();
        $data = $this->storage->load($clientIP); // Use strategy
        $now = time();
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        $data['request_times'] = array_filter($data['request_times'] ?? [], fn($t) => $now - $t < 1);
        $data['request_times'][] = $now;

        $data['user_agents'][$ua] = ($data['user_agents'][$ua] ?? 0) + 1;
        $data['last_seen'] = $now;
        $data['total_requests'] = ($data['total_requests'] ?? 0) + 1;

        $limitExceededThisRequest = count($data['request_times']) > $this->getLimitPerSecond();

        if ($limitExceededThisRequest) 
        {
            if (isset($data['timeout_start']) && ($now - $data['timeout_start']) < $this->getTimeoutSeconds()) 
            {
                $data['violations'] = ($data['violations'] ?? 0) + 1;
            } 
            else 
            {
                $data['timeout_start'] = $now;
                $data['violations'] = 1; 
            }
        }

        if (!empty($data['violations'])) {
            if ($data['violations'] >= $this->getMaxViolations()) {
                $this->storage->save($clientIP, $data); // Use strategy
                return true; // Blocked
            }
            if ($limitExceededThisRequest) {
                $this->storage->save($clientIP, $data); // Use strategy
                http_response_code(429);
                header('Retry-After: ' . $this->getTimeoutSeconds());
                exit;
            }
        }
        
        $this->storage->save($clientIP, $data); // Use strategy
        return false; 
    }

    /**
     * Get the client IP address.
     * This method can remain in ThrottleGuard as it's not storage-specific.
     * @return string
     */
    public function getClientIP(): string // Made public for potential use in index.php for logging
    {
        // Prefer X-Forwarded-For if behind a proxy, otherwise fallback to REMOTE_ADDR
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipAddresses = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim(end($ipAddresses)); // Get the last IP address
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }
        return 'unknown';
    }
}
