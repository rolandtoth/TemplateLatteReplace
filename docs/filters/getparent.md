## getparent

Simply returns the given PageArray's parent page, eg. when using with getpages filter.

```php
<p>
    {('template=portfolio-items'|getpages|getparent)->title}
</p>
```