## imageattrs

Adds width, height and alt attributes to an image.

By default the filter adds an "alt" attribute with image description or an empty tag if no description is provided. To disable add `-alt` to the first parameter.

```php
<img src="{$page->img_field}" {$page->img_field|imageattrs|noescape} />
<img src="{$page->img_field}" {$page->img_field|imageattrs:'-alt'|noescape} />
```