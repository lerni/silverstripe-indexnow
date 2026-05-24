<?php

namespace IndexNow\Extensions;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use SilverStripe\Core\Extension;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Versioned\Versioned;
use GuzzleHttp\Exception\RequestException;

/**
 * Submits a URL to the IndexNow API after publish (Versioned objects) or after
 * write (non-Versioned objects that expose an AbsoluteLink).
 *
 * @extends Extension<\SilverStripe\ORM\DataObject>
 */
class IndexNowExtension extends Extension
{
    private static int $timeout = 5;

    /**
     * Fires for Versioned objects when a draft is published to live.
     */
    public function onAfterPublish(): void
    {
        if (!Director::isLive()) {
            return;
        }

        $owner = $this->getOwner();

        if ($owner->hasField('ShowInSearch') && !$owner->ShowInSearch) {
            return;
        }

        $url = $owner->AbsoluteLink();
        if (!$url || !($apiKey = $this->getApiKey())) {
            return;
        }

        $this->submitUrl($url, $apiKey);
    }

    /**
     * Fires for Versioned objects when a live page is unpublished (becomes 404).
     */
    public function onAfterUnpublish(): void
    {
        if (!Director::isLive()) {
            return;
        }

        $url = $this->getOwner()->AbsoluteLink();
        if (!$url || !($apiKey = $this->getApiKey())) {
            return;
        }

        $this->submitUrl($url, $apiKey);
    }

    /**
     * Fires for non-Versioned objects before deletion so the URL can still be resolved.
     * Versioned objects are handled via onAfterUnpublish instead.
     */
    public function onBeforeDelete(): void
    {
        if (!Director::isLive()) {
            return;
        }

        $owner = $this->getOwner();

        if ($owner->hasExtension(Versioned::class)) {
            return;
        }

        $url = $owner->AbsoluteLink();
        if (!$url || !($apiKey = $this->getApiKey())) {
            return;
        }

        $this->submitUrl($url, $apiKey);
    }

    /**
     * Fires for non-Versioned objects on every write.
     * Versioned objects are handled via onAfterPublish instead.
     */
    public function onAfterWrite(): void
    {
        if (!Director::isLive()) {
            return;
        }

        $owner = $this->getOwner();

        if ($owner->hasExtension(Versioned::class)) {
            return;
        }

        $url = $owner->AbsoluteLink();
        if (!$url || !($apiKey = $this->getApiKey())) {
            return;
        }

        $this->submitUrl($url, $apiKey);
    }

    private function getApiKey(): ?string
    {
        $key = SiteConfig::current_site_config()->getResolvedIndexNowAPIKey();

        return $key ?: null;
    }

    private function submitUrl(string $url, string $apiKey): void
    {
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
