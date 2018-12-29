## ifpage

An IF condition to check if a given page exists. Also sets a `$p` variable if yes. You can use `else` for the false part.

```php
{ifpage $pages->get('name=services')}
    <h1>{$p->title|noescape}</h1>
    <p>
        Page exists!
    </p>
{else}
    <p>Services page doesn't exist.</p>
{/ifpage}
```