## minify

Minifies markup by removing whitespace and optionally performs other tweaks. Can be applied partially to sections or individual HTML tags.

By default the macro removes whitespace between HTML tags that are not on the same line to handle situations like bold text followed by italic. This is a safe way to reduce the overall size of the markup.

**Experimental features**

**NOTE**: experimental features were removed in v0.5.1.

If parameter `true` is passed the macro will perform additional minifications, eg. removing quotes from attributes that has no spaces or removing default attributes like `type="text"`. According to the markup these tweaks can lead to undesired results so use it only on your own risk. For advanced minification check [ProCache](https://processwire.com/api/modules/procache/) (commercial) or [AIOM](http://modules.processwire.com/modules/all-in-one-minify/).

Applying as an n:macro:

```php
<div n:minify>
    ...
</div>
```

Applying as an inner macro to remove whitespace between `li` elements:

```php
<ul n:inner-minify>
    <li n:foreach="..."></li>
</div>
```

Applying to a region (whole layout in this example) and enabling experimental settings (by passing`true`):

```php
{minify true}
<!DOCTYPE html>
...
</html>
{/minify}
```