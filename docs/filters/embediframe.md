## embediframe

Generates markup for responsive iframe embed. 

Apply the filter to the embed url (not the video url nor the whole embed code).
Determines aspect ratio from width/height parameters and adds it as an inline style.
Requires a few lines of additional CSS, see below.

### Parameters (array):

- **width**: iframe width in pixels (get from embed code, default: 560)
- **height**: iframe height in pixels (get from embed code, default: 315)
- **upscale**: if set to false, the iframe won't be bigger than the original width
- **attr**: attributes to add to the `iframe` tag
- **wrapAttr**: attributes to add to the wrapper tag. Note: overrides the default `class="embed-wrap"` attribute
- **srcAttr**: use this to change the default `src` tag to something else, eg. `data-src` for lazy loading purposes
- **urlParams**: use to add extra parameters to the source url, eg. "?autoplay=1" for videos


```php
{var $url = 'https://www.youtube.com/embed/IHqnLQy9R1A'}

{$url|embediframe|noescape}
{$url|embediframe: array('attr' => 'class="lazyload" allowfullscreen')|noescape}
{$url|embediframe: array('attr' => 'class="lazyload" allowfullscreen', 'wrapAttr' => 'class="embed-wrap video"', 'srcAttr' => 'data-src')|noescape}
```

### CSS:

```CSS
.embed-wrap {
  margin: 0 auto; /* optional */
}
.embed-wrap > div {
  position: relative;
  height: 0;
}
.embed-wrap iframe {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  border: none;
}
```

### Generated markup

**Default (no parameters)**:

```html
<div class="embed-wrap"><div style="padding-bottom:56.25%"><iframe width="560" height="315" src="https://www.youtube.com/embed/IHqnLQy9R1A"></iframe></div></div>
```

**With various parameters**:

```html
<div class="embed-wrap video"><div style="padding-bottom:56.25%"><iframe width="560" height="315" data-src="https://www.youtube.com/embed/IHqnLQy9R1A" class="lazyload" allowfullscreen></iframe></div></div>
```

### Default values

You can set default values by adding a `$view->embediframeDefaults` array, eg. in ready.php file:

```php
$view->embediframeDefaults = array(
    'width'    => 600,
    'height'   => 480,
    'wrapAttr' => 'class="iframe-wrapper"',
);
```

### Tips

- adjust the wrapper element width to set the iframe width (in CSS)
- to lazyload iframes width [Lazysizes](https://github.com/aFarkas/lazysizes) add the class `lazyload` and set `srcAttr` to `data-src` (plus include lazysizes.js too)