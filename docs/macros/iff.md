# [Macro] iff

An IF condition that sets a variable "$x" to the value of the condition output. In the example below "$x" will be "$page->body", making it easier to replace "body" if you need for example "title" instead.

```php
<div n:iff="$page->body">
    {$x|noescape}
</div>
```

```php
{iff $page->body}
    {$x|noescape}
{/iff}
```