<?php
namespace BotDetection;

/**
 * Class ZipBomber
 * 
 * Delivers a gzipped file as a response, simulating a zip bomb.
 * 
 * @package BotDetection
 */
class ZipBomber
{
    /**
     * Delivers the gzipped zip bomb file to the client.
     * 
     * Checks if the file exists, and if so, sends appropriate headers to prompt a download.
     * If the file doesn't exist, it sends a 404 HTTP response.
     * 
     * @return void
     */
	public function deliver()
	{
		$path = __DIR__ . '/../../payload/zipbomb.gz';

        if (!file_exists($path)) 
        {
            header('HTTP/1.1 404 Not Found');
			exit('File not found.');
		}

		header('Content-Type: application/gzip');
		header('Content-Disposition: attachment; filename="update.gz"');
		readfile($path);
		exit;
	}
}
