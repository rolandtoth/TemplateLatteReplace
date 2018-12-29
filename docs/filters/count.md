# [Filter] count

Returns the number of items in PageArray. Can be used for checking if there's any items to show (eg. with n:if).

```php
{var $services = 'template=services, parent.template=home'}
<div n:if="($services|count)">
    {* do something if there are items in $services PageArray *}
</div>
```