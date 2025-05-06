# TrafficGuardian

TrafficGuardian is a powerful tool for detecting and preventing unwanted bot traffic and excessive requests to your website. It combines bot detection and rate limiting to provide a robust security solution for your web application.

## Features:
- Detects malicious bots and allows/blocks them based on user-agent strings.
- Rate-limits requests to your server to prevent abuse (throttling based on IP address).
- Uses a whitelist/blacklist system for known good and bad bots.
- Stores throttle data in a log for persistent tracking.
- Implements timeouts for users who exceed the request limits.

## Installation

Clone the repository:

```bash
git clone https://github.com/Blizzard-fs/TrafficGuardian.git
```

Install the dependencies (none required, pure PHP):

```bash
# No dependencies currently
```

## Usage

### 1. Integrating `BotDetector` in Your Application

To integrate the bot detection and throttling mechanism in your PHP application, you need to create an instance of `BotDetector` and check if the request is suspicious.

```php
// Assuming BotDetector and ThrottleGuard are autoloaded or included properly
require 'path/to/BotDetector.php';

$botDetector = new BotDetector();

if ($botDetector->isSuspicious()) {
    // Action: Block the request, send an HTTP response, or log the incident
    header("HTTP/1.1 403 Forbidden");
    echo "Request is suspicious and has been blocked.";
    exit;
}

// Continue processing legitimate requests
echo "Request is legitimate.";
```

### 2. Rate Limiting and Bot Detection

The `BotDetector` class performs two key checks:

* **Bot Detection**: It checks the `User-Agent` of the incoming request against a predefined blacklist and whitelist of bots.
* **Rate Limiting**: It tracks the request rate for each IP address and throttles requests that exceed the allowed rate.

### 3. Customizing the Bot Lists

You can update the bot lists by modifying the `$botWhitelist` and `$botBlacklist` arrays within the `BotDetector` class. Add or remove bot user-agent strings to suit your needs.

```php
$botDetector = new BotDetector();

// Customize bot lists:
$botDetector->setBotBlacklist([
    'malicious-bot',
    'bad-bot',
    // Add more bots here
]);

$botDetector->setBotWhitelist([
    'Googlebot',
    'Bingbot',
    // Add more SEO bots here
]);
```

### 4. Modifying Rate Limits

To adjust the rate limits for throttling requests, modify the `ThrottleGuard` class, specifically the `getLimitPerSecond()` and `getTimeoutSeconds()` methods. You can set your desired rate limits as follows:

```php
// ThrottleGuard example:
$throttleGuard = new ThrottleGuard();
$throttleGuard->setLimitPerSecond(10); // Allow 10 requests per second
$throttleGuard->setTimeoutSeconds(60); // Timeout for 60 seconds after exceeding the limit
```

### 5. Log Storage

Throttle data (timestamps, user-agent counts, violations) are stored in JSON files, ensuring persistent tracking across requests. You can configure the log file storage location and format by modifying the `ThrottleGuard` class.

The logs will be stored in a directory called `Logs/`, with each IP having its own file named according to the IP address (with dots replaced by hyphens). This makes it easy to track request activity per user.

```bash
Logs/
  192-168-1-1.json
  10-0-0-1.json
```

### 6. Viewing and Purging Logs

To view or purge the logs, you can either write a custom script that reads and deletes logs based on your preferences, or manually check the logs stored in the `Logs/` folder.

```php
// Example to view logs for a specific IP
$ip = '192.168.1.1';
$logFile = 'Logs/' . str_replace('.', '-', $ip) . '.json';

if (file_exists($logFile)) {
    $logData = json_decode(file_get_contents($logFile), true);
    print_r($logData);
}

// Example to purge logs for a specific IP
if (file_exists($logFile)) {
    unlink($logFile); // Deletes the log for that IP
}
```

## Contributions

If you have ideas for improvements or bug fixes, feel free to fork the repository and create a pull request. All contributions are welcome!

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

Enjoy a more secure, bot-resistant, and well-throttled web application with TrafficGuardian!
