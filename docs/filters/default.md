# [Filter] default

Returns the specified default value if empty or falsy value is passed. You can use it for example to substitute simple if-else conditions.

```php
<div>
    {$page->product_description|default:'No description is available for this product.'}
</div>
```