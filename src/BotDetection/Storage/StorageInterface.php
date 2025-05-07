<?php

namespace BotDetection\Storage;

/**
 * Interface StorageInterface
 * Defines the structure for storage strategies used by ThrottleGuard.
 */
interface StorageInterface
{
    /**
     * Load data for a given identifier (e.g., IP address).
     *
     * @param string $identifier The identifier (e.g., IP address).
     * @return array The loaded data, or an empty array if not found.
     */
    public function load(string $identifier): array;

    /**
     * Save data for a given identifier.
     *
     * @param string $identifier The identifier (e.g., IP address).
     * @param array $data The data to save.
     * @return void
     */
    public function save(string $identifier, array $data): void;
}
