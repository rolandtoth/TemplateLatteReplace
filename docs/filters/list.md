# [Filter] list

List array items with a separator.

Similar to the built-in "implode" filter but accepts string too.

A separator string and a HTML tag can be passed to surround each item. Empty items will be skipped.

```php
{array($page->title, ($page->modified|date:'%Y'), $page->name)|list:'|','span'|noescape}
```

Result:

```html
<span>Home</span>|<span>2017</span>|<span>en</span>
```