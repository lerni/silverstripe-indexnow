<?php
namespace IndexNow\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\TextField;

class SiteConfigExtension extends Extension
{
    private static array $db = [
        'IndexNowActive' => 'Boolean',
        'IndexNowAPIKey' => 'Varchar(255)'
    ];

    public function updateCMSFields($fields)
    {
        $fields->addFieldsToTab('Root.IndexNow', [
            CompositeField::create(
                CheckboxField::create('IndexNowActive', 'Enable IndexNow')
                    ->setDescription('Enable or disable the IndexNow service for all pages - this will allow your site to automatically notify search engines of content changes.'),
            )->setTitle('Enabled'),
            TextField::create('IndexNowAPIKey', 'API Key')
                ->setDescription('Your IndexNow API key - you can get one from the IndexNow website <a href="https://www.bing.com/indexnow/getstarted#implementation">https://www.bing.com/indexnow/getstarted#implementation</a>.')
        ]);
    }


    public function onBeforeWrite()
    {
        // Save the API key in a txt file in public
        if($this->owner->IndexNowActive && $this->owner->IndexNowAPIKey) {
            $apiKey = $this->owner->IndexNowAPIKey;
            $filePath = PUBLIC_PATH . '/indexnow_api_key.txt';
            file_put_contents($filePath, $apiKey);
        } else {
            // If not active, remove the file
            $filePath = PUBLIC_PATH . '/indexnow_api_key.txt';
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }
}
