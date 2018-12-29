## savetemp

Allows saving temporary data to reuse later with `$gettemp()`.

```php
<img n:if="($page->images->get('tags=logo')|savetemp)" src="{($gettemp()->width(700)|savetemp)->url}"
 width="{$gettemp()->width}" height="{$gettemp()->height}" />
```

In this example the img tag is outputted only if there is an image with a tag "logo", and right here we save the image object to a variable using `savetemp`. When adding the src tag we don't need to repeat the `$page->images->get('tags=logo')` part again but use `$gettemp()` to retrieve it. After that we resize and save again, to use the resized image to add width and height attributes.

The filter doesn't modify the data it was called on. This means you can use it when chaining filters to save a certain state, eg:

```php
<p>{('HELLO')|lower|savetemp|firstUpper|append:' World!'} - saved: {$gettemp()}</p>
// outputs: Hello World! - saved: hello
```

Macros `iff` and `setvar` provide similar features but `savetemp` is more versatile as it can be added more easily to existing markup.