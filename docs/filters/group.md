# [Filter] group

Allows creating groups from a PageArray based on a page field value. This can be handy if you would like to output pages grouped by category or other value.

Returns an array of:

- key: sanitized field value, eg. to use for class names
- title: the human-readable name of the group (field value)
- items: array of Pages in the group

```php
{foreach ($page->children()->sort('sort')|group:'section') as $group}
    <h2>{$group['title']}</h2>

    <div class="{$group['key']}">
        {foreach $group['items'] as $p}
            <div>
                <a href="{$p->url}">{$p->title}</a>
            </div>
        {/foreach}
    </div>
{/foreach}
```