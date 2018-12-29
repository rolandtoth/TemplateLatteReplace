# [Macro] pages

Simlar to the "page" filter but returns a PageArray (loaded to "$pArr") instead of a single page. Under the hood it uses the $pages->find() API command.


```php
<ul n:pages="'template=basic-page,limit=10'" n:inner-foreach="$pArr as $p">
    <li>{$p->title}</li>
</ul>
```

```php
{pages 'template=basic-page,limit=10'}
<ul n:inner-foreach="$pArr as $p">
    <li>{$p->title}</li>
</ul>
{/pages}
```