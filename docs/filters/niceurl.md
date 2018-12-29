# [Filter] niceurl

Removes "http(s)" and/or "www" from a link, useful for outputting shorter links.

```php
{* remove www and http *}
<a href="{$page->httpUrl}">{$page->httpUrl|niceurl}</a>
{* remove "http" only *}
<a href="{$page->httpUrl}">{$page->httpUrl|niceurl:'http'}</a>
{* remove "www" only *}
<a href="{$page->httpUrl}">{$page->httpUrl|niceurl:'www'}</a>
{* remove trailing "/" *}
<a href="{$page->httpUrl}">{$page->httpUrl|niceurl:'/'}</a>
{* remove "www" and trailing "/" *}
<a href="{$page->httpUrl}">{$page->httpUrl|niceurl:'www/'}</a>
```