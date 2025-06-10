<?php

namespace IndexNow\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\Tab;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Control\Director;

class PageExtension extends Extension
{
    private static array $db = [
        'DisableIndexNow' => 'Boolean',
    ];

    private static array $defaults = [
        'DisableIndexNow' => false
    ];

    public function updateSettingsFields($fields): void
    {
        $fields->addFieldsToTab('Root.Settings', [
            Tab::create('IndexNow', 'IndexNow')
        ], 'Visibility');

        $fields->addFieldsToTab('Root.Settings.IndexNow', [
            CheckboxField::create('DisableIndexNow', 'Disable IndexNow for this page')
                ->setDescription(SiteConfig::current_site_config()->IndexNowActive ? 'If enabled, this page will be included in the IndexNow service.' : 'IndexNow is disabled globally, so this setting will not have any effect.')
                ->setDisabled(!SiteConfig::current_site_config()->IndexNowActive), // Disable if global setting is off
        ]);
    }

    public function onAfterPublish()
    {
        // If IndexNow is gloablly enabled and enable for this page, we can proceed
        if (SiteConfig::current_site_config()->IndexNowActive && !$this->owner->DisableIndexNow) {
            // Logic to handle IndexNow for this page
            $this->submitUrlForIndexNow();
        }
    }

    public function submitUrlForIndexNow()
    {
        try {
            $siteConfig = SiteConfig::current_site_config();
            $apiKey = $siteConfig->IndexNowAPIKey;
            $endpoint = 'https://api.indexnow.org'; // Endpoint for IndexNow API BING
            if (!$apiKey) {
                throw new \Exception('IndexNow API Key or Host is not set.');
            }
            // Prepare the URL to submit
            $url = $this->owner->AbsoluteLink();
            $keyLocation = $siteConfig->IndexNowBaseURL . '/indexnow_api_key.txt';

            $requestURL = $endpoint . '/indexnow?url=' . $url . '&key=' . $apiKey . '&keyLocation=' . $keyLocation;
            // GET request to the IndexNow API with getContent and HTTP headers
            $response = file_get_contents($requestURL, false, stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => "Content-Type: application/json\r\n"
                ]
            ]));
            // Check if the response is false
            if ($response === false) {
                throw new \Exception('Failed to submit URL to IndexNow.');
            }
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during the submission
            error_log('IndexNow submission failed: ' . $e->getMessage(), E_USER_ERROR);
        }
    }
}
