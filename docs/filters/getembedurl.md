## getembedurl

Retrieves embed url from video urls. Currently Youtube and Vimeo are supported. Can be useful to use with `embediframe` filter.

```php
{$page->video_url|getembedurl|embediframe|noescape}
```