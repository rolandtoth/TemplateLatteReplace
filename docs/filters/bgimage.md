## bgimage

Adds an inline style with background-image: url(...).

```php
<div {$page->featured_image->size(1180,320)|bgimage|noescape}></div>
// result:
// <div style="background-image: url('/site/assets/files/1/image.1180x320.jpg')"></div>
```