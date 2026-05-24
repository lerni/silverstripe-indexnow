# Silverstripe IndexNow

Fork of [pixelpoems/silverstripe-indexnow](https://github.com/pixelpoems/silverstripe-indexnow) with various refactoring.

This module integrates the [IndexNow](https://www.indexnow.org/) protocol into Silverstripe CMS, automatically notifying search engines when pages are published.

* [Requirements](#requirements)
* [Installation](#installation)
* [Configuration](#configuration)
* [Changes from upstream](#changes-from-upstream)
* [Reporting Issues](#reporting-issues)

## Requirements

* Silverstripe CMS ^6.0

## Installation
```
composer require pixelpoems/silverstripe-indexnow
```

## Configuration

Set your IndexNow API key via environment variable (recommended):

```
INDEXNOW_API_KEY="your-api-key-here"
```

Alternatively, enter the key in **Settings → IndexNow** within the CMS. When an environment variable is set, the CMS field becomes read-only.

URLs are submitted to IndexNow when:
- An API key is configured (via `.env` or CMS)
- The environment is `live`
- The object exposes an `AbsoluteLink()`

The extension fires on relevant lifecycle events:

| Event | Object type | Trigger |
|---|---|---|
| Published to live | Versioned (e.g. pages) | `onAfterPublish` |
| Unpublished from live | Versioned | `onAfterUnpublish` |
| Created or updated | Non-Versioned (e.g. DataObjects with a URL) | `onAfterWrite` |
| Deleted | Non-Versioned | `onBeforeDelete` |

For Versioned objects, `ShowInSearch` is respected if the field exists. `IndexNowExtension` can be applied to any DataObject that has an `AbsoluteLink()` method, not just pages.

The module serves the key verification file dynamically at `/{key}.txt` — no file system writes needed.

### Suggested module

- [dorsetdigital/silverstripe-canonical](https://github.com/dorsetdigital/silverstripe-canonical) — Recommended for canonical URL management alongside IndexNow

## Changes from upstream

- **Unified IndexNowExtension** — `PageExtension` and `UrlifyedObjExtension` merged into a single `IndexNowExtension`. Versioned-aware: uses `onAfterPublish`/`onAfterUnpublish` for versioned objects and `onAfterWrite`/`onBeforeDelete` for plain DataObjects. Applicable to any object with `AbsoluteLink()`.
- **Unpublish and delete notifications** — Search engines are now pinged on unpublish and delete, allowing them to remove stale URLs from their index.
- **ShowInSearch instead of DisableIndexNow** — Uses the built-in `ShowInSearch` field on `SiteTree` instead of a custom `DisableIndexNow` checkbox. No extra DB column, no extra CMS UI per page.
- **No IndexNowActive toggle** — The API key presence is the on/off gate. No key = no submissions.
- **Environment variable support** — API key can be set via `INDEXNOW_API_KEY` in `.env`. Falls back to the DB field in SiteConfig.
- **Guzzle HTTP** — Replaced `file_get_contents` with `GuzzleHttp\Client` for proper HTTP status codes, exceptions, and timeouts.
- **Dynamic key verification** — Serves `{key}.txt` via HTTP middleware instead of writing a file to `public/`. No `.gitignore` entry needed.
- **Proper logging** — Uses Silverstripe's `LoggerInterface` instead of `error_log()`.
- **Translations** — All CMS strings use `_t()` with German translations in `lang/de.yml`.
- **Silverstripe 6 only** — Dropped Silverstripe 5 support.
- **Extension targets SiteTree** — Applied to `SilverStripe\CMS\Model\SiteTree` instead of `Page` for coverage of all page types.
- **PSR-4 namespace** — Fixed to `IndexNow\` matching actual class declarations.

## Reporting Issues

Please [create an issue](https://github.com/lerni/silverstripe-indexnow/issues) for any bugs you've found, or features you're missing.

