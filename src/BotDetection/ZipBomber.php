<?php
namespace BotDetection;

class ZipBomber
{
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
