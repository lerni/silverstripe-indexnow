# Silverstripe / Index Now

[![stability-beta](https://img.shields.io/badge/stability-beta-33bbff.svg)](https://github.com/mkenney/software-guides/blob/master/STABILITY-BADGES.md#beta)

This module adds a simple cookie consent banner to your Silverstripe website. It uses the [CookieConsent v3](https://cookieconsent.orestbida.com/) script.
You can use it with the [Fluent](https://github.com/tractorcow-farm/silverstripe-fluent) translation module.

* [Requirements](#requirements)
* [Installation](#installation)
* [Configuration](#configuration)
* [Reporting Issues](#reporting-issues)

## Requirements

* Silverstripe CMS ^5.0
* Silverstripe Framework >=5.0

## Installation
```
composer require pixelpoems/silverstripe-indexnow
```

## Configuration
[//]: # (ToDo)

- Add your IndexNow API within the siteconfig, with no additional whitespace or newlines.
- Enable the indexing of your site by setting the `IndexNowEnabled` checkbox in the siteconfig.
- Add `/public/indexnow_api_key.txt` to your .gitignore file to prevent it from being committed to your repository.

## Reporting Issues

Please [create an issue](https://github.com/pixelpoems/silverstripe-indexnow/issues) for any bugs you've found, or
features you're missing.

