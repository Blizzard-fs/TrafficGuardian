<?php

namespace BotDetection\Models;

/**
 * Represents the data tracked per client (IP address) for rate limiting purposes.
 */
class ClientRequestData
{
    /** @var array<int, int> List of timestamps of recent requests (unix timestamp). */
    private array $requestTimes;

    /** @var array<string, int> Counter per user agent. */
    private array $userAgents;

    /** @var int|null Timestamp of the last request. */
    private ?int $lastSeen;

    /** @var int Total number of requests received from this client. */
    private int $totalRequests;

    /** @var int|null Timestamp when the current timeout period started. */
    private ?int $timeoutStart;

    /** @var int Number of times the rate limit has been exceeded within a timeout period. */
    private int $violations;

    /**
     * Constructor for ClientRequestData.
     *
     * @param array<int, int> $requestTimes Initial request times.
     * @param array<string, int> $userAgents Initial user agent counts.
     * @param int|null $lastSeen Timestamp of the last seen request.
     * @param int $totalRequests Total requests made.
     * @param int|null $timeoutStart Timestamp when the current timeout started.
     * @param int $violations Number of violations.
     */
    public function __construct(
        array $requestTimes = [],
        array $userAgents = [],
        ?int $lastSeen = null,
        int $totalRequests = 0,
        ?int $timeoutStart = null,
        int $violations = 0
    ) {
        $this->requestTimes = $requestTimes;
        $this->userAgents = $userAgents;
        $this->lastSeen = $lastSeen;
        $this->totalRequests = $totalRequests;
        $this->timeoutStart = $timeoutStart;
        $this->violations = $violations;
    }

    // Getters
    /**
     * @return array<int, int>
     */
    public function getRequestTimes(): array
    {
        return $this->requestTimes;
    }

    /**
     * @return array<string, int>
     */
    public function getUserAgents(): array
    {
        return $this->userAgents;
    }

    public function getLastSeen(): ?int
    {
        return $this->lastSeen;
    }

    public function getTotalRequests(): int
    {
        return $this->totalRequests;
    }

    public function getTimeoutStart(): ?int
    {
        return $this->timeoutStart;
    }

    public function getViolations(): int
    {
        return $this->violations;
    }

    // Methods to manipulate the data
    public function addRequestTime(int $timestamp): void
    {
        $this->requestTimes[] = $timestamp;
    }

    /**
     * Filters request_times, keeping only entries within the given $periodInSeconds.
     * @param int $periodInSeconds The time window in seconds to keep requests for.
     */
    public function filterRequestTimes(int $periodInSeconds): void
    {
        $now = time();
        $this->requestTimes = array_filter(
            $this->requestTimes,
            fn($timestamp) => ($now - $timestamp) < $periodInSeconds
        );
    }

    public function incrementUserAgent(string $userAgent): void
    {
        $this->userAgents[$userAgent] = ($this->userAgents[$userAgent] ?? 0) + 1;
    }

    public function setLastSeen(int $timestamp): void
    {
        $this->lastSeen = $timestamp;
    }

    public function incrementTotalRequests(): void
    {
        $this->totalRequests++;
    }

    public function setTimeoutStart(int $timestamp): void
    {
        $this->timeoutStart = $timestamp;
    }

    public function incrementViolations(): void
    {
        $this->violations++;
    }

    public function setViolations(int $count): void
    {
        $this->violations = $count;
    }

    /**
     * Converts the object to an array, useful for storage.
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'request_times' => $this->requestTimes,
            'user_agents' => $this->userAgents,
            'last_seen' => $this->lastSeen,
            'total_requests' => $this->totalRequests,
            'timeout_start' => $this->timeoutStart,
            'violations' => $this->violations,
        ];
    }

    /**
     * Creates a ClientRequestData object from an array.
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['request_times'] ?? [],
            $data['user_agents'] ?? [],
            $data['last_seen'] ?? null,
            $data['total_requests'] ?? 0,
            $data['timeout_start'] ?? null,
            $data['violations'] ?? 0
        );
    }
}
