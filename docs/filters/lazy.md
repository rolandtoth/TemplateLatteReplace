## lazy

Adds `data-src` tag to img tag to be used with [Lazysizes](https://github.com/aFarkas/lazysizes). 

Requires lazysizes.js added manually and adding the "lazyload" class to the image. For IE11 [respimage polyfill](https://github.com/aFarkas/respimage) is also needed.

Parameter `$divisor` can be used to set the placeholder size (defaults to 4).

```php
<img src="{$page->images->first()->width(900)|lazy:3|noescape}" alt="" class="lazyload" />
// output:
// <img src="/site/assets/files/1/image.300x0.jpg" data-src="/site/assets/files/1/mobile.900x0.jpg" class="lazyload" alt="">
```