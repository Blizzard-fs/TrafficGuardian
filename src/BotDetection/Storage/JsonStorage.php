<?php

namespace BotDetection\Storage;

class JsonStorage implements StorageInterface 
{
    private string $logPath;

    /**
     * FileStorageStrategy constructor.
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
                error_log("Warning: Could not create log directory: " . $this->logPath);
            }
        }
    }

    private function getFilePath(string $identifier): string
    {
        // we use . and : to sanitize both IPv4 and IPv6;
        $filename = str_replace(['.', ':'], '-', $identifier) . '.json';
        return rtrim($this->logPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
    }

    public function load(string $identifier): array
    {
        $filePath = $this->getFilePath($identifier);

        if (!file_exists($filePath)) {
            return [];
        }

        $jsonData = file_get_contents($filePath);
        if ($jsonData === false) {
            return []; // TODO: Add logging
        }

        $data = json_decode($jsonData, true);
        return is_array($data) ? $data : [];
    }

    public function save(string $identifier, array $data): void
    {
        $filePath = $this->getFilePath($identifier);
        $jsonData = json_encode($data, JSON_PRETTY_PRINT);

        if ($jsonData === false) 
        {
            error_log("Error: json_encode failed for IP: " . $identifier);
            return;
        }

        if (file_put_contents($filePath, $jsonData) === false) 
        {
            error_log("Error: file_put_contents failed for path: " . $filePath);
        }
    }
}
