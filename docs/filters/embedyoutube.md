## embedyoutube

Generates markup for the [Light Youtube Embed](https://www.labnol.org/internet/light-youtube-embeds/27941/) method.

You'll need to add the JavaScript and CSS from the site linked above.

```php
{$page->youtube_link|embedyoutube}
```

The filter handles standard youtube URLs and playlist URLs too.