# [Macro] setvar

This is an alternative to the built-in "var" macro and allows setting a variable "inline", that is, adding to a tag for example.

```php
<div n:setvar="url,$p->url">
    {$url}
</div>
```