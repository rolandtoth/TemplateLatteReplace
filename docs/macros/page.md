# [Macro] page

If you need to switch to a page quickly, use the "page" macro. It accepts a page ID (or a selector). It automatically sets the "$p" variable too. Under the hood it uses the $pages->get() API command.


```php
<div n:page="1039">
    {$p->title}
</div>
```

```php
{page 'template=home'}
    {$p->title}
{/page}
```