<?php

namespace BotDetection;

class ThrottleGuard
{
    protected string $logPath = __DIR__ . '/../../Logs/';
    protected int $limitPerSecond = 10;
    protected int $timeoutSeconds = 10;
    protected int $maxViolations = 5;

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
        $data = $this->load();
        if (!isset($data['violations'])) return false;

        return $data['violations'] >= $this->getMaxViolations();
    }

    /**
     * Check if the rate limit has been exceeded.
     *
     * @return bool
     */
    public function isRateLimitExceeded(): bool
    {
        $data = $this->load();
        $now = time();
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        // Filter out old timestamps older than 1 sec;
        $data['request_times'] = array_filter($data['request_times'] ?? [], fn($t) => $now - $t < 1);
        $data['request_times'][] = $now;

        // Track User-Agent counts;
        $data['user_agents'][$ua] = ($data['user_agents'][$ua] ?? 0) + 1;
        $data['last_seen'] = $now;
        $data['total_requests'] = ($data['total_requests'] ?? 0) + 1;

        // Check count if exceeding limit per second;
        if (count($data['request_times']) > $this->getLimitPerSecond()) 
        {
            // If timeout exists and is retry is still active add violation else set timeout and output response;
            if (isset($data['timeout_start']) && ($now - $data['timeout_start']) < $this->getTimeoutSeconds()) 
            {
                $data['violations'] = ($data['violations'] ?? 0) + 1;
            } 
            else 
            {
                $data['timeout_start'] = $now;
            }

            $this->save($data);
            http_response_code(429);
            header('Retry-After: ' . $this->getTimeoutSeconds());
            exit;
        }

        $this->save($data);
        return false;
    }

    /**
     * Load the data for the current IP address from the JSON log file.
     *
     * @return array
     */
    protected function load(): array
    {
        $ip = $this->getClientIP();
        $file = $this->getPath($ip);

        if (!file_exists($this->getLogPath())) mkdir($this->getLogPath(), 0755, true);
        if (!file_exists($file)) return [];

        return json_decode(file_get_contents($file), true) ?? [];
    }

    /**
     * Save the data for the current IP address to the JSON log file.
     *
     * @param array $data
     */
    protected function save(array $data): void
    {
        $ip = $this->getClientIP();
        $file = $this->getPath($ip);
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * Get the file path for the current IP address's log file.
     *
     * @param string $ip
     * @return string
     */
    protected function getPath(string $ip): string
    {
        $filename = str_replace('.', '-', $ip) . '.json';
        return $this->getLogPath() . $filename;
    }

    /**
     * Get the client IP address.
     *
     * @return string
     */
    protected function getClientIP(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
}
