# [Filter] getpage

This is a "filter" version of the macro "page" (see above) that makes really easy to reference a page by ID or selector.

Note: use parenthesis to access the returned Page object's methods.

```php
<p>
    {(1|getpage)->title}
</p>
```