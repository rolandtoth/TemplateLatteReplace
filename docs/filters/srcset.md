# [Filter] srcset

Generate image variations and markup for the "data-srcset" or "data-bgset" attribute (requires [Lazysizes](https://github.com/aFarkas/lazysizes)).

**Image sizes**

- image sizes need to be in a width x height format, eg. `540x320`.
- separate set of image sizes with a comma, eg. `540x320,270x160`
- use `0` if you would like to keep the existing image ratio, eg. `800x0` or `0x640`
- multipliers or divisors can be used, eg. `300x200,*3,/2`. Here the second set will be `900x600`, the third one `150x100`. The filter will rearrange the sets in the final markup (from smallest to largest)
- the first image size in a set should not be a multiplier nor a divisor (1)

You'll need to add the `lazyload` class to the image to make Lazysizes work, and also the `data-sizes` tag. If you need a low quality placeholder or a fallback image in a noscript tag you will have to generate them manually.

(1) You can use the keyword `original` to for the width and height of the image:

```
original,600x0,300x0 => 900x600, 600x400, 300x200 (if the input image is 900x600px)
```

Example:

```php
<img data-srcset="{$page->images->first()|srcset:'540x320,*3,/2'|noescape}" data-sizes="auto" class="lazyload" alt=""/>
```

Generated markup:

```html
<img data-srcset="/site/assets/files/1170/img.270x160.jpg 270w,/site/assets/files/1170/img.540x320.jpg 540w,/site/assets/files/1170/img.1620x960.jpg 1620w" data-sizes="auto" class="lazyload" alt=""/>
```

Image resize options array can be passed as optional second parameter (read more [here](https://processwire.com/api/fieldtypes/images/)):

```php
<img data-srcset="{$page->images->first()|srcset:'1200x600,/2,/4', array('upscaling'=>true)|noescape}" data-sizes="auto" class="lazyload" alt=""/>
```

Note that the following CSS rule should be present if you are using data-sizes="auto": `img[data-sizes="auto"] { display: block; width: 100%; }` (read more at the [Lazysizes](https://github.com/aFarkas/lazysizes) documentation).

You can also use the filter to generate markup for the [bgset](https://github.com/aFarkas/lazysizes/tree/gh-pages/plugins/bgset) Lazysizes plugin:

```php
<div data-bgset="{$img|srcset:'1200x360,/2,/3'|noescape}" class="lazyload" data-sizes="auto"></div>
```

The filter adds an associative array of image variation widths and urls, sorted in ascending order, to a temporary variable that you can get using `$gettemp()` - see the `savetemp` filter for more info. You can use this to output the smallest or largest image variation's url, for example for LQIP's or noscript tags:

```php
<noscript>
    <img src="{$gettemp()|last}" alt=""/>
</noscript>
```