<?php

namespace BotDetection\Storage;

use BotDetection\Models\ClientRequestData;

class JsonStorage implements StorageInterface
{
    private string $logPath;

    /**
     * JsonStorage constructor.
     *
     * @param string|null $logPath The path to the log directory. Defaults to project_root/Logs/.
     */
    public function __construct(?string $logPath = null)
    {
        if ($logPath === null) 
        {
            $this->logPath = __DIR__ . '/../../../Logs/';
        } 
        else 
        {
            $this->logPath = $logPath;
        }

        if (!is_dir($this->logPath)) 
        {
            if (!mkdir($this->logPath, 0755, true) && !is_dir($this->logPath)) 
            {
                error_log("JsonStorage Warning: Could not create log directory: " . $this->logPath);
            }
        }
    }

    /**
     * Generates the full file path for a given identifier.
     *
     * @param string $identifier The identifier (e.g., IP address).
     * @return string The full file path.
     */
    private function getFilePath(string $identifier): string
    {
        $filename = str_replace(['.', ':'], '-', $identifier) . '.json';
        return rtrim($this->logPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
    }

    /**
     * {@inheritdoc}
     */
    public function load(string $identifier): ?ClientRequestData
    {
        $filePath = $this->getFilePath($identifier);

        if (!file_exists($filePath)) 
        {
            return null; 
        }

        $jsonData = file_get_contents($filePath);
        if ($jsonData === false) 
        {
            error_log("JsonStorage Error: Failed to read file for identifier: " . $identifier . " at path: " . $filePath);
            return null;
        }

        $dataArray = json_decode($jsonData, true);
        if (!is_array($dataArray)) 
        {
            error_log("JsonStorage Error: Failed to decode JSON or JSON is not an array for identifier: " . $identifier . " at path: " . $filePath);
            return null;
        }

        return ClientRequestData::fromArray($dataArray);
    }

    /**
     * {@inheritdoc}
     */
    public function save(string $identifier, ClientRequestData $data): void
    {
        $filePath = $this->getFilePath($identifier);
        $jsonData = json_encode($data->toArray(), JSON_PRETTY_PRINT);

        if ($jsonData === false) 
        {
            error_log("JsonStorage Error: json_encode failed for identifier: " . $identifier . ". Error: " . json_last_error_msg());
            return;
        }

        if (file_put_contents($filePath, $jsonData) === false) 
        {
            error_log("JsonStorage Error: file_put_contents failed for identifier: " . $identifier . " at path: " . $filePath);
        }
    }
}
