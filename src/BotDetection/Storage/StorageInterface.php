<?php

namespace BotDetection\Storage;

use BotDetection\Models\ClientRequestData;

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
     * @return ClientRequestData|null The loaded data object, or null if not found.
     */
    public function load(string $identifier): ?ClientRequestData;

    /**
     * Save data for a given identifier.
     *
     * @param string $identifier The identifier (e.g., IP address).
     * @param ClientRequestData $data The data object to save.
     * @return void
     */
    public function save(string $identifier, ClientRequestData $data): void;
}
