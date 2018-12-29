## breadcrumb

Generates markup (unordered HTML list) for breadcrumbs. Note that no separator is added, use CSS for that.

Pass an array of options to fine-tune:

- **root**: root page to start the breadcrumb from (selector or Page). Default: Home page 
- **addHome**: whether to prepend the Home page. Default: true
- **addCurrent**: append the current page. Default: false
- **addCurrentLink**: whether to add link when appending the current page. Default: false
- **class**: CSS class to add to the "ul" tag. Default: "breadcrumb". Pass an empty string to remove class.
- **id**: CSS id to add to the "ul" tag. Default: none (no id added)
- **addAttributes**: add "data-page" attributes to 'LI' tags with the corresponding page id. Default: false


```php
<div>
    {$page|breadcrumb|noescape}
    {$page|breadcrumb:array('addCurrent' => true, 'addHome' => false, 'addCurrentLink' => true)|noescape}
    {$page|breadcrumb:array('root' => 1038, 'addCurrent' => true, 'id' => 'breadcrumb-top', 'class' => 'breadcrumb custom-class')|noescape}
</div>
```

### Default values

You can set default values by adding a `$view->breadcrumbDefaults` array, eg. in ready.php file:

```php
$view->breadcrumbDefaults = array(
    'addHome'    => false,
    'addAttributes'   => true
);
```php