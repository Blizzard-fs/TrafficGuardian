<?php
spl_autoload_register(function($class) {
	$prefix = 'BotDetection\\';
	$baseDir = __DIR__ . '/src/BotDetection/';

	if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
		return;
	}

	$relativeClass = substr($class, strlen($prefix));
	$file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

	if (file_exists($file)) {
		require $file;
	}
});