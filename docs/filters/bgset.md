## bgset

Adds `data-bgset` tag to elements to be used with [Lazysizes bgset plugin](https://github.com/aFarkas/lazysizes/tree/gh-pages/plugins/bgset). 

Requires lazysizes.js and ls.bgset.js added manually and adding the "lazyload" class to the element. For IE11 [respimage polyfill](https://github.com/aFarkas/respimage) is also needed.

Parameter `$divisor` can be used to set the placeholder size (defaults to 4).

```php
<div {$page->images->first()->width(900)|bgset:3|noescape} class="lazyload"></div>
// output:
// <div style="background-image: url('/site/assets/files/1/image.200x75.jpg')" data-bgset="/site/assets/files/1/image.600x225.jpg" class="lazyload"></div>
```