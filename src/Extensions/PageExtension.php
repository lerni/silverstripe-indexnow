<?php

namespace IndexNow\Extensions;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use SilverStripe\Core\Extension;
use SilverStripe\Control\Director;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Core\Injector\Injector;
use GuzzleHttp\Exception\RequestException;

/**
 * @extends Extension<\SilverStripe\CMS\Model\SiteTree>
 */
class PageExtension extends Extension
{
    private static int $timeout = 5;

    public function onAfterPublish(): void
    {
        if (!Director::isLive()) {
            return;
        }

        $siteConfig = SiteConfig::current_site_config();
        $apiKey = $siteConfig->getResolvedIndexNowAPIKey();
        if (!$apiKey || !$this->getOwner()->ShowInSearch) {
            return;
        }

        try {
            $url = $this->getOwner()->AbsoluteLink();

            $client = new Client(['timeout' => static::config()->get('timeout')]);
            $client->get('https://api.indexnow.org/indexnow', [
                'query' => [
                    'url' => $url,
                    'key' => $apiKey,
                ],
            ]);
        } catch (RequestException $exception) {
            Injector::inst()->get(LoggerInterface::class)->warning(
                'IndexNow submission failed: ' . $exception->getMessage(),
            );
        }
    }
}
