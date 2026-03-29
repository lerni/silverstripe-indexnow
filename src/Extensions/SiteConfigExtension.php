<?php

namespace IndexNow\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\TextField;
use SilverStripe\Core\Environment;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * @extends Extension<SiteConfig>
 */
class SiteConfigExtension extends Extension
{
    private static array $db = [
        'IndexNowAPIKey' => 'Varchar',
    ];

    public function updateCMSFields($fields): void
    {
        $envKey = Environment::getEnv('INDEXNOW_API_KEY');

        $apiKeyField = TextField::create(
            'IndexNowAPIKey',
            _t(self::class . '.IndexNowAPIKey', 'API Key'),
        );

        if ($envKey) {
            $apiKeyField->setValue($envKey);
            $apiKeyField->setDescription(_t(
                self::class . '.IndexNowAPIKeyDescriptionEnv',
                'API key is set via environment variable INDEXNOW_API_KEY.',
            ));
            $apiKeyField = $apiKeyField->performReadonlyTransformation();
        } else {
            $apiKeyField->setDescription(_t(
                self::class . '.IndexNowAPIKeyDescription',
                '<a href="https://www.bing.com/indexnow/getstarted#implementation">Get an IndexNow API key</a>. Set it here or preferably per environment variable INDEXNOW_API_KEY.',
            ));
        }

        $fields->addFieldsToTab('Root.IndexNow', [
            $apiKeyField,
        ]);
    }

    public function getResolvedIndexNowAPIKey(): string
    {
        return Environment::getEnv('INDEXNOW_API_KEY') ?: ($this->getOwner()->IndexNowAPIKey ?: '');
    }
}
