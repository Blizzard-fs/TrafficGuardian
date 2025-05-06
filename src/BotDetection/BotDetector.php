<?php
namespace BotDetection;

class BotDetector
{
    protected ThrottleGuard $throttleGuard;

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

    public function __construct() 
    {
        $this->setThrottleGuard(new ThrottleGuard()); 
    }

	public function isSuspicious(): bool
	{
		return $this->isBotUserAgent() || $this->getThrottleGuard()->isRateLimitExceeded();
	}

    protected function isBotUserAgent(): bool
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        if (preg_match('/' . implode('|', $this->getBotWhitelist()) . '/i', $userAgent)) {
            return false;
        }

        return preg_match('/' . implode('|', $this->getBotBlacklist()) . '/i', $userAgent);
    }

    //TODO: If timeout exceeds set timeout header and see if the user continues unwanted behaviour. > then block the user;
    public function getBotWhitelist(): array
    {
        return $this->botWhitelist;
    }

    public function getBotBlacklist(): array
    {
        return $this->botBlacklist; 
    }
    
    public function setThrottleGuard(?ThrottleGuard $throttleGuard): void 
    {
        $this->throttleGuard = $throttleGuard;
    }

    public function getThrottleGuard(): ?ThrottleGuard
    {
        return $this->throttleGuard; 
    }
}
