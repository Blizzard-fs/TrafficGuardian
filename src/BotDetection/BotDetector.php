<?php
namespace BotDetection;

/**
 * Class BotDetector
 * 
 * Detects bots based on user agent strings and manages rate limiting.
 * 
 * @package BotDetection
 */
class BotDetector
{
    /**
     * @var ThrottleGuard $throttleGuard The throttle guard instance for rate limiting.
     */
    protected ThrottleGuard $throttleGuard;

    /**
     * @var array $botBlacklist List of bot user agents to detect.
     */
    protected array $botBlacklist = [
        'bot',
        'crawl',
        'slurp',
        'spider',
        'httpclient',
        'python-requests',
        'okhttp',
        'libwww',
        'java/',
        'ruby',
        'go-http-client',
        'curl',
        'wget',
        'php/',
        'perl',
        'winhttp',
        'HTTrack',
        'Fetch',
        'urlgrabber',
        'http_request2',
        'feedfetcher',
        'python-urllib',
        'node-fetch',
        'axios',
        'httpget',
        'ZmEu',
        'WordPress/',
        'Indy Library',
        'ScanAlert',
        'Nutch',
        'ApacheBench',
        'Nikto',
        'clshttp',
        'EmailCollector',
        'EmailSiphon',
        'WebCopier',
        'WebDownloader',
        'SiteSnagger',
        'Xenu',
        'python',
        'bbot',
        'Masscan',
        'nessus',
        'nmap',
        'Arachni',
        'dirbuster',
        'webshag',
        'ratproxy',
        'paros',
        'jaascois',
        'AppEngine-Google',
        'lwp-trivial',
        'GT::WWW',
        'CheckHost',
        'heritrix',
        'LinkWalker',
        'SiteSucker',
        'Teleport',
        'NetcraftSurveyAgent',
        'vultr',
        'downthemall',
        'axios',
        'python',
        'cfnetwork',
        'phantomjs',
        'headless',
        'puppeteer',
        'selenium',
        'chrome-lighthouse',
        'lighthouse',
        'cypress',
        'bash',
        'PowerShell'
    ];

    /**
     * @var array $botWhitelist List of allowed bot user agents (e.g., search engine bots).
     */
    protected array $botWhitelist = [
        'Googlebot',
        'Googlebot-Image',
        'Googlebot-News',
        'Googlebot-Video',
        'Bingbot',
        'Slurp',               // Yahoo
        'DuckDuckBot',
        'Baiduspider',
        'YandexBot',
        'YandexImages',
        'Yeti',                // Naver
        'facebot',             // Facebook crawler
        'ia_archiver',         // Alexa
        'Twitterbot',
        'Applebot',
        'LinkedInBot',
        'SemrushBot',
        'AhrefsBot',
        'MJ12bot',
        'DotBot',
        'Sogou',
        'Exabot',
        'SeznamBot',
        'CCBot',               // Common Crawl
        'PetalBot',            // Huawei
        'Qwantify',
        'DataForSeoBot',
        'MojeekBot'
    ];

    /**
     * BotDetector constructor.
     * Initializes the throttle guard.
     */
    public function __construct() 
    {
        $this->setThrottleGuard(new ThrottleGuard()); 
    }

    /**
     * Checks if the current request is suspicious based on user agent or rate limit.
     * 
     * @return bool True if the request is suspicious, false otherwise.
     */
	public function isSuspicious(): bool
	{
		return $this->isBotUserAgent() || $this->getThrottleGuard()->isRateLimitExceeded();
	}

    /**
     * Checks if the user agent belongs to a bot (either blacklisted or not whitelisted).
     * 
     * @return bool True if the user agent is a bot, false otherwise.
     */
    protected function isBotUserAgent(): bool
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        if (preg_match('/' . implode('|', $this->getBotWhitelist()) . '/i', $userAgent)) {
            return false;
        }

        return preg_match('/' . implode('|', $this->getBotBlacklist()) . '/i', $userAgent);
    }

    /**
     * Returns the list of bot user agents that are whitelisted.
     * 
     * @return array The whitelist of bot user agents.
     */
    public function getBotWhitelist(): array
    {
        return $this->botWhitelist;
    }

    /**
     * Returns the list of bot user agents that are blacklisted.
     * 
     * @return array The blacklist of bot user agents.
     */
    public function getBotBlacklist(): array
    {
        return $this->botBlacklist; 
    }

    /**
     * Sets the throttle guard instance for rate limiting.
     * 
     * @param ThrottleGuard|null $throttleGuard The throttle guard instance.
     */
    public function setThrottleGuard(?ThrottleGuard $throttleGuard): void 
    {
        $this->throttleGuard = $throttleGuard;
    }

    /**
     * Gets the throttle guard instance.
     * 
     * @return ThrottleGuard|null The throttle guard instance.
     */
    public function getThrottleGuard(): ?ThrottleGuard
    {
        return $this->throttleGuard; 
    }
}
