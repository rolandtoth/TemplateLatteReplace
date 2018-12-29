# [Filter] onlynumbers

Removes everything other than numbers. You could also use the built-in "replaceRE" filter.

```php
<p>
    {$page->phone|onlynumbers}
</p>
```