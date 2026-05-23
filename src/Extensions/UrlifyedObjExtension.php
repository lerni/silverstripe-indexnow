<?php

namespace IndexNow\Extensions;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use SilverStripe\Core\Extension;
use SilverStripe\Control\Director;
use App\Extensions\UrlifyExtension;
use SilverStripe\Core\Config\Config;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Core\Injector\Injector;
use GuzzleHttp\Exception\RequestException;

/**
 * @extends Extension<\SilverStripe\ORM\DataObject>
 */
class UrlifyedObjExtension extends Extension
{
    private static int $timeout = 5;

    public function onAfterWrite(): void
    {
        if (!Director::isLive()) {
            return;
        }

        $owner = $this->getOwner();

        if (!$owner->hasExtension(UrlifyExtension::class)) {
            return;
        }

        $siteConfig = SiteConfig::current_site_config();
        $apiKey = $siteConfig->getResolvedIndexNowAPIKey();
        if (!$apiKey) {
            return;
        }

        if (!$owner->canView()) {
            return;
        }

        $url = $owner->AbsoluteLink();
        if (!$url) {
            return;
        }

        try {
            $client = new Client(['timeout' => Config::inst()->get(self::class, 'timeout')]);
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
