<?php

namespace BotDetection;

use BotDetection\Storage\StorageInterface;
use BotDetection\Models\ClientRequestData;

class ThrottleGuard
{
    protected int $limitPerSecond;
    protected int $timeoutSeconds;
    protected int $maxViolations;
    private StorageInterface $storage;

    /**
     * ThrottleGuard constructor.
     *
     * @param StorageInterface $storage The storage mechanism for request data.
     * @param int $limitPerSecond Max requests allowed per second.
     * @param int $timeoutSeconds Duration of timeout in seconds after exceeding the limit.
     * @param int $maxViolations Max violations before an IP is considered blocked (within timeouts).
     */
    public function __construct(
        StorageInterface $storage,
        int $limitPerSecond = 10,
        int $timeoutSeconds = 10,
        int $maxViolations = 5
    ) {
        $this->storage = $storage;
        $this->limitPerSecond = $limitPerSecond;
        $this->timeoutSeconds = $timeoutSeconds;
        $this->maxViolations = $maxViolations;
    }

    // Getters and Setters for configuration parameters
    public function getLimitPerSecond(): int
    {
        return $this->limitPerSecond;
    }

    public function setLimitPerSecond(int $limit): void
    {
        $this->limitPerSecond = $limit;
    }

    public function getTimeoutSeconds(): int
    {
        return $this->timeoutSeconds;
    }

    public function setTimeoutSeconds(int $seconds): void
    {
        $this->timeoutSeconds = $seconds;
    }

    public function getMaxViolations(): int
    {
        return $this->maxViolations;
    }

    public function setMaxViolations(int $max): void
    {
        $this->maxViolations = $max;
    }

    /**
     * Gets the client's IP address.
     * Prefers X-Forwarded-For if behind a proxy, otherwise falls back to REMOTE_ADDR.
     *
     * @return string The client's IP address or 'unknown'.
     */
    public function getClientIP(): string
    {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) 
        {
            $ipAddresses = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim(end($ipAddresses));
        } 
        elseif (!empty($_SERVER['REMOTE_ADDR'])) 
        {
            return $_SERVER['REMOTE_ADDR'];
        }

        return 'unknown';
    }

    /**
     * Checks if the current client IP is blocked due to excessive violations.
     *
     * @return bool True if blocked, false otherwise.
     */
    public function isBlocked(): bool
    {
        $clientIP = $this->getClientIP();

        if ($clientIP === 'unknown') return false;

        $clientData = $this->storage->load($clientIP);

        if ($clientData === null) 
        {
            return false;
        }

        return $clientData->getViolations() >= $this->getMaxViolations();
    }

    /**
     * Checks if the current request exceeds the rate limit.
     * Manages request tracking, violation counting, and applying timeouts or blocks.
     *
     * @return bool True if the client is now considered blocked (max violations reached),
     * false if the request is allowed.
     * Exits with HTTP 429 if rate limit is exceeded but not yet max violations.
     */
    public function isRateLimitExceeded(): bool
    {
        $clientIP = $this->getClientIP();
        if ($clientIP === 'unknown') 
        {
            return false;
        }

        $now = time();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown_ua';

        $clientData = $this->storage->load($clientIP);
        if ($clientData === null) 
        {
            $clientData = new ClientRequestData();
        }

        $clientData->filterRequestTimes(1);
        $clientData->addRequestTime($now);

        $clientData->incrementUserAgent($userAgent);
        $clientData->setLastSeen($now);
        $clientData->incrementTotalRequests();

        $requestsInLastSecond = count($clientData->getRequestTimes());
        $limitExceededThisRequest = $requestsInLastSecond > $this->getLimitPerSecond();

        if ($limitExceededThisRequest) {
            if ($clientData->getTimeoutStart() !== null && ($now - $clientData->getTimeoutStart()) < $this->getTimeoutSeconds()) 
            {
                $clientData->incrementViolations();
            } 
            else 
            {
                $clientData->setTimeoutStart($now);
                $clientData->setViolations(1);           
            }
        }

        if ($clientData->getViolations() >= $this->getMaxViolations()) 
        {
            $this->storage->save($clientIP, $clientData);
            return true;
        }

        if ($limitExceededThisRequest) 
        {
            $this->storage->save($clientIP, $clientData);
            http_response_code(429);
            header('Retry-After: ' . $this->getTimeoutSeconds());
            exit;
        }

        $this->storage->save($clientIP, $clientData);

        return false;
    }
}
