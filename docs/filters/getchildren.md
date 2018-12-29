# [Filter] getchildren

Returns child pages, applied to a selector or a Page. A selector string can be passed to filter children.

```php
{foreach (1023|getchildren) as $p}
    {$p->title|noescape}
{/foreach}
```

```php
{foreach ($page|getchildren:'limit=4') as $p}
    {include 'partials/article.latte', p => $p}
{/foreach}
```

Alias `children` can also be used:

```php
{if (1088|children:'template=blog')->count()}
    ...
{/if}
```