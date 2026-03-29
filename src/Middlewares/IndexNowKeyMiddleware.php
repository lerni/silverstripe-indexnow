<?php

namespace IndexNow\Middlewares;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Control\Middleware\HTTPMiddleware;

class IndexNowKeyMiddleware implements HTTPMiddleware
{
    public function process(HTTPRequest $request, callable $delegate)
    {
        $url = ltrim($request->getURL(), '/');

        // Match root-level {key}.txt — key must be 8-128 hex chars or dashes
        if (preg_match('/^([a-zA-Z0-9-]{8,128})\.txt$/', $url, $matches)) {
            $siteConfig = SiteConfig::current_site_config();
            $apiKey = $siteConfig->getResolvedIndexNowAPIKey();

            if ($apiKey && $matches[1] === $apiKey) {
                $response = HTTPResponse::create($apiKey);
                $response->addHeader('Content-Type', 'text/plain; charset=utf-8');

                return $response;
            }
        }

        return $delegate($request);
    }
}
