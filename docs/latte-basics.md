# Latte basics

Without going into details, here are some basic information to make starting with Latte easier. See more at [Latte](https://latte.nette.org/).


## Outputting content

Use curly braces to print variables:

```php
<h1>{$page->title}</h1>
```

By default Latte escapes content to make things more secure. Sometimes you need to disable that, then use the "noescape" filter. Filters are added after a pipe sign:

```php
<h1>{$page->body|noescape}</h1>
```

Use "noescape" filter when you need to output HTML, for example when printing content of a CKEditor field.

There are many built-in filters and you even create your own ones. See the "Filters" section below.



## Layouts and blocks

The main layout file contains the HTML skeleton for your site, with blocks in it. These blocks are filled with content from the view files. For example, on the homepage "views/home.latte" will be used, which extends the "@layout.latte" default layout.

*Sample layout ("@default.latte"):*

```php
<!DOCTYPE html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$page->title|noescape}</title>
    <link rel="stylesheet" media="screen,projection,tv" href="{$config->urls->templates}styles/main.css">
    <link rel="shortcut icon" href="{$config->urls->templates}favicon.ico">
</head>

<body class="page-{$page->id} template-{$page->template->name}">

<div>
	{block header}
		Welcome on my homepage!
	{/block}

	{block intro}{/block}

    <div id="content">
        {include content}
    </div>

    {include 'partials/footer.latte', pageName => $page->name}
</div>

{block scripts}
    <script src="http://code.jquery.com/jquery-latest.min.js"></script>
{/block}

<script src="{$config->urls->templates}scripts/main.js"></script>

</body>
</html>
```

In this layout there are several blocks:

- **header**: by default it says "Welcome on my homepage!". If you define the same block in a view file (eg. "basic-page.latte"), then it will show that content instead (overrides the default). To keep the default and append content, add `{include parent}` after the opening "{block}" tag.

- **intro**: it has no content by default. If you define the same block in a view file, it will show that content. If it's omitted, then it will be skipped.

- **content**: this is required on all view files because it is an "include". Latte will show an error if omitted.

There is also a footer included at the bottom. It's another .latte file and it's path is "views/partials/footer.latte". Notice that a variable is passed to this view file named "pageName", which will be available in footer.latte as "$pageName".

*Sample "basic-page.latte":*


```php
{block scripts}
    {include parent}
    <script src="{$config->urls->templates}scripts/lightbox.js"></script>
{/block}

{block header}{/block}

{block content}
    <h1>{$page->title|noescape}</h1>

    <div>
        {$page->body|noescape}
    </div>
{/block}
```

Three blocks are defined:

- **scripts**: this will keep the default block contents of the layout file, plus adds lightbox.js to it

- **header**: this is empty here, which means the default content of the main layout file will be removed

- **content**: this is a required block so it has to be added

As you can see, the order of the blocks is irrelevant. If you place HTML outside of blocks, they won't be shown on the page.

## Using another layout file

You can use other layout than the default. Just create a new layout file and add this to the view file as the first line.

```php
{layout 'layouts/@layout-sidebar.latte}
```

This examples will load the `@layout-sidebar.latte` layout file from the "views/layouts" directory.

When using multiple layouts, it is recommended to create a directory for them. See the module settings how to set this up.

Read more on blocks [here](https://latte.nette.org/en/macros#toc-blocks).


## Macros

Macros can be used for conditionals or loops. You can create custom macros too, see below.

```php
{if $page->body}
    <h1>{$page->body|noescape}</h1>
{/if}
```

```php
{foreach $page->repeater_field as $item}
    <img src="{$item->images->first()->url}" />
{/foreach}
```

Instead of using separate tags for the conditional, you can use it directly on the HTML tag.

```php
<h1 n:if="$page->body">{$page->body|noescape}</h1>
```

You can use foreach on the parent element too. The following example will output as many "li" tags as the number of children pages $page has:

```php
<ul n:inner-foreach="$page->children() as $p">
    <li>{$p->title}</li>
</ul>
```

## Iterator

When being inside a loop, you can check the current iteration number by using $iterator->counter. Additionally, there are pre-defined iterator helpers like $iterator->isFirst() or $iterator->isLast(). [See more here](https://latte.nette.org/en/macros#toc-loops).

## n:class macro

When you need to add a class only if a condition evaluates to true, you can use the n:class macro.

*Example: add 'active' class only if the $page name is 'services', plus always add classes "page" and "template-default":*

```php
<p n:class="$page->name == 'services' ? 'active', 'page template-default'"></p>
```

## Caching

Latte builds a php file from .latte files and use them until the source .latte files are unchanged. Turning on template cache usually adds a bit of extra cache but using Latte alone is fast enough. If it's not enough, ProCache can be used for further speed improvements.

Note that in the standalone version of Latte cannot use the "{cache}" macro. You should use ProcessWire's markup cache instead.

## How to clear the cache

Until version 0.4.9 the only way to clear cache was visiting the module's settings page and checking the "Clear cache" checkbox and save the module.

From v0.4.9 you can append a "clearcache" GET parameter to the URL to clear the cache (for SuperUsers only).

## Cache location

The module's cache is located in `/site/assets/cache/TemplateLatteReplace`. It is safe to clear or remove this directory.


## Using PHP in latte files

Latte allows using PHP in latte files, even if it's not a good practice (you should do it in template php files).

*Example: using var_dump() in a view file:*

```php
// basic-page.latte
{php var_dump($page->title)}
```

If your php expression returns HTML then use "noescape" filter.


## Change view file in template file

By default the module loads the view file identically named as the template php file ("contact.php -> views/contact.latte"). However, in certain cases you may need to override that. To do that, specify the "viewFile" in your template file:

```php
if ($page->id == 1035) {
    $view->viewFile = 'services.latte';
    // this will also work:
    $view->viewFile = 'services';
}
```

This will load "views/services.latte" instead. This feature enables dynamic switching of view files, so you can use the same (php) template for multiple pages and render different view files for example.


## Disable prepending template path and view directory to viewFile

If viewFile begins with "//", templates path and viewDir will be not prepended. The starting "//" is removed from the $viewFile by the module. This can be useful when you need to place latte files outside the views directory set in module config.

```php
// double slash indicates not to prepend $config->paths->templates and view directory
$view->viewFile = '//' . $config->paths->root . 'archives.latte';
```

## Return JSON encoded markup

Using $view->json_encode will instruct the module to return JSON encoded string (markup). This can be beneficial for ajax responses, for example.

Possible values are `true` and PHP's json_encode options:

```php
$view->json_encode = true;
$view->json_encode = JSON_PRETTY_PRINT;
$view->json_encode = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE;
```


## Using wireRenderFile

There's nothing special when using wireRenderFile, just save it to a variable.

```php
// contact.php
$view->bodyHtml = wireRenderFile("./newsletter_body", array('title' => $page->title, 'body' => $page->body));
```

## Other stuff

See the official Latte documentation for more stuff like

- setting variables
- setting default values for variables
- capturing html section into variable
- switching latte syntax (eg. to double curly braces for script tags)
- etc.

## Global variables

You can set global variables in _init.php because it will be always loaded (provided if you load it in your config.php "prependTemplateFile").

Example: setting $contactPage global variable
```php
// _init.php
$view->contactPage = $pages->get(1044);
```


## Filters

You can make your own filters by using "$view->addFilters($name, $callback)". From v2.4 there are some additional filters available too (see below).

*Example: add "activeclass" filter:*

```php
$view->addFilter('activeclass', function ($currentPage) {
    $page = wire('page');

    return ($page == $currentPage || $page->parentsUntil(1)->has($currentPage)) ? 'active' : '';
});
```

*Usage in view file:*

```php
<body class="{$page|activeclass}">
```

Every filter gets the preceding variable as the first parameter. In this case, the parameter "$currentPage" is "$page", because the filter was called on "$page" ("$page|activeclass").

To specify parameters use the ":" character:

```php
<body class="{$page->title|truncate:20}">
```

This will truncate the title at 20 characters ("truncate" is a default filter, [see all here](https://latte.nette.org/en/filters)).

**Where to place filters?**

The best place for filters is "/site/ready.php", or another file that ProcessWire always loads.

**How to call filters in PHP files?**

Both built-in and custom macros can be used in PHP files too with $view->invokeFilter($name, $parametersArray).

*Example: apply the built-in "truncate" filter on a string*

```php
$view->invokeFilter('truncate', array('Lorem ipsum dolorem sit', 5));
// output: "Lore…"
```

Note: using a scalar value for the second parameter is OK if your filter doesn't need any parameter.

*Example: apply the built-in "upper" filter on a string*

```php
$view->invokeFilter('upper', 'Lorem ipsum');
// output: "LOREM IPSUM"
```


## Custom macros

You can also add your custom macros by using "$view->addMacro()". See the file "_macros.php" for details.



## Additional filters and macros (from v2.4)

There are a few additional (convenience) filters and macros to use in view files.

See additional macros and filters [here](https://github.com/rolandtoth/TemplateLatteReplace/wiki/Additional-macros) and [here](https://github.com/rolandtoth/TemplateLatteReplace/wiki/Additional-filters).


## String translation

ProcessWire's Language Translator cannot parse strings from Latte files so a workaround is needed.

**_strings.php**

The module uses "/site/templates/_strings.php" file as a default textdomain where you need to list all the strings you use in view files.

Note: as of v0.2.8 the default textdomain can be set in module settings page or manually using "wire('config')->defaultTextdomain = '/site/templates/_translations.php'".

Create a "_strings.php" file in "/site/templates/" directory, then add your strings to it.

For strings without context, use "__()", and use "_x() if you need to set a context:

*_strings.php example:*

```txt
__('Read more')
__('Please select')
_x('Learn more', 'Context')
```

Now go the Language Translator in ProcessWire admin and in each language click on the "Translate File" button and select "_strings.php" from the list. Now you can enter translations for these strings.

In your view files use the `_` function (underscore) to get the translated string:

```php
<a href="#">{_'Read more'}</a>
```


## Using context or textdomain

If you need to set a context or textdomain, use the following syntax in your view files:

```php
<a href="#">{_'Submit', 'Form'}</a>
<a href="#">{_'Submit', 'Form', '/site/templates/_form-strings.php'}</a>
<a href="#">{_'Submit', null, '/site/templates/_form-strings.php'}</a>
```

Using the example above will load the translation for string "Submit" using the context "Form", and using "/site/templates/_form-strings.php" as the textdomain (second line).

Set context to `null` if you need no context but do need a textdomain.

In "_strings.php" (or "_form-strings.php") you'll need to use "_x" instead of double underscores. Also note that you have to add the context too:

```txt
_x('Submit', 'Form')
```


## Using plurals

The module comes with another helper function called `__p()`:

View file:

```php
{__p("add %d item", "add %d items", $page->children()->count())}
```

Using the above line in a view file will print "add %d item" or "add %d items", according to the number of child pages $page has.

**Value replacement**

To add replacments for the placeholder, pass an array of them:

```php
<p>{__p("add %d %s item", "add %d %s items", 24, array(24, 'extraordinary'))}</p>
```

This will print "add 24 extraordinary items". Passing "1" instead of "24" would print "add 1 extraordinary item".

To translate these strings, you'll need to add two lines to "_strings.php":

```txt
__('add %d %s item')
__('add %d %s items')
```

**Context and textdomain with plurals**

You can use context and textdomain with plurals too. The syntax in view file is:

```php
__p("singular", "plural", $count [, $replacementsArray], $context, $textdomain)
```


**Using translator helper functions in template files**

You can use `_t()` for translation and `__p()` (or `_p()`, see below) for pluralization in your template php files:

```php
$greetings = _t('Hello!');
$successMessage = _t('Success! You made it again.', 'Form');
$title = __p('%s orange', '%s oranges', $count, array('clockwork'));
```

You can also use "_p" (single underscore) as an alias for "__p".

## Default translations on a non-multilanguage site

From v0.4.3 it is possible to translate strings even if the site is non-multilanguage. Such situation can occur eg. when using partials from another project that has translation "keys" instead literals. In such cases you can add a `$config->default_translations` associative array and set translations there. Use nested arrays for strings with contexts.

```php
// _t('phone_number');
// _t('please_add_at_least_%d_characters', 'Forms');

// ready.php or config.php
$config->default_translations = array(
    'phone_number' => 'Phone number',
    'Forms' => array(
        'please_add_at_least_%d_characters' => 'Please add at least %d characters'
    )
);
```