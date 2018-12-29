## renderpager

Returns a pagination markup (pager) when applied to a PageArray. Accepts one parameter: a number (numPageLinks) or an array to override the defaults. The alias "pager" can also be used.

```php
{$pArr|renderpager|noescape}
{$pArr|renderpager:2|noescape}
{$pArr|renderpager:array('nextItemLabel' => 'next')|noescape}
{$pArr|renderpager:$customPaginationSettings|noescape}
```

In the examples above $customPaginationSettings is an array that you can set eg. in ready.php:

```php
$view->customPaginationSettings = array(
    'numPageLinks'       => 3, // Default: 10
    'getVars'            => null, // Default: empty array
    'baseUrl'            => array(), // Default: empty
    'listMarkup'         => "<ul class='pagination'>{out}</ul>",
    'itemMarkup'         => "<li class='{class}'>{out}</li>",
    'linkMarkup'         => "<a href='{url}'><span>{out}</span></a>",
    'nextItemLabel'      => 'next page',
    'previousItemLabel'  => 'previous page',
    'separatorItemLabel' => '',
    'separatorItemClass' => '',
    'nextItemClass'      => 'next',
    'previousItemClass'  => 'previous',
    'lastItemClass'      => 'last',
    'currentItemClass'   => 'active'
);
```

### Default values

You can set common default values by creating a `$view->renderPagerDefaults` array, eg. in ready.php file. This way all pagers will use these settings but you can override them individually in view files if needed.

```php
$view->renderPagerDefaults = array(
    'nextItemLabel' => 'Next',
    'previousItemLabel' => 'Prev'
);
```