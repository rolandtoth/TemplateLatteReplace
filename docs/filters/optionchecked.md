# [Filter] optionchecked

Check if a Select Options field on a page has a given option selected.

Consider a field named "page_options" with a selectable option `3=show_header|Show header`. In this case you can use one of the following to check if it's checked on a given page:

* `page_options.3`
* `page_options.show_header`
* `page_options.Show header`

```php
{if ($page|optionchecked:'page_options.show_header')}
<header>
    <p>This line is visible only if the $page has "show_header" checked/selected.</p>
</header>
{/if}
```

The filter also checks whether the page template contains the field so there will be no error when applying it to pages with different templates (eg. when listing various type of pages).