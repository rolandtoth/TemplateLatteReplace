Template Latte Replace
================

[Latte](https://latte.nette.org/) template engine support for [ProcessWire CMS](http://processwire.com/).


## Install

1. Install the module

1. Create a "views" directory in "/site/templates/" directory

1. Create a main layout file in the "views" directory, eg. "@layout.latte".

1. For each of your template php files, create a view file in the "views" directory. For example, create "views/basic-page.latte" for "basic-page.php"

*Note: you can customize view files' directory, $view variable name and main layout in module settings, see below.*

## Uninstall

Simply uninstall the module through the admin.

Note that uninstalling the module will cause an existing site disfunctional because all content is printed through the view files.

## Usage

Template php files will serve as controllers which load the identically named view (.latte) files.

These "controllers" provide data for view files through a $view object.

*Example: pass $subPages to view file and loop through it. In the view file `$view->subPages` become `$subPages`*.

```php
// home.php
$view->subPages = $pages->get(1)->children();
```

In "views/home.latte" view file:

```php
<ul n:inner-foreach="$subPages as $p">
    <li>{$p->title}</li>
</ul>
```


## Latte basics

Without going into details, here are some basic information to make starting with Latte easier. See more at [Latte](https://latte.nette.org/).


### Outputting content

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



### Layouts and blocks

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

### Using another layout file

You can use other layout than the default. Just create a new layout file and add this to the view file as the first line.

```php
{layout 'layouts/@layout-sidebar.latte}
```

This examples will load the `@layout-sidebar.latte` layout file from the "views/layouts" directory.

When using multiple layouts, it is recommended to create a directory for them. See the module settings how to set this up.

Read more on blocks [here](https://latte.nette.org/en/macros#toc-blocks).


### Macros

Macros can be used for conditionals or loops.

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

#### Iterator

When being inside a loop, you can check the current iteration number by using $iterator->counter. Additionally, there are pre-defined iterator helpers like $iterator->isFirst() or $iterator->isLast(). [See more here](https://latte.nette.org/en/macros#toc-loops).

#### n:class macro

When you need to add a class only if a condition evaluates to true, you can use the n:class macro.

*Example: add 'active' class only if the $page name is 'services', plus always add classes "page" and "template-default":*

```php
<p n:class="$page->name == 'services' ? 'active', 'page template-default'"></p>
```

### Caching

Latte builds a php file from .latte files and use them until the source .latte files are unchanged. Turning on template cache usually adds a bit of extra cache but using Latte alone is fast enough. If it's not enough, ProCache can be used for further speed improvements.

Note that in the standalone version of Latte cannot use the "{cache}" macro. You should use ProcessWire's markup cache instead.


### Using PHP in latte files

Latte allows using PHP in latte files, even if it's not a good practice (you should do it in template php files).

*Example: using var_dump() in a view file:*

```php
// basic-page.latte
{php var_dump($page->title)}
```

If your php expression returns HTML then use "noescape" filter.


### Change view file in template file

By default the module loads the view file identically named as the template php file ("contact.php -> views/contact.latte"). However, in certain cases you may need to override that. To do that, specify the "viewFile" in your template file:

```php
if ($page->id == 1035) {
    $view->viewFile = 'services';
}
```

This will load "views/services.latte" instead. This feature enables dynamic switching of view files, so you can use the same template for multiple pages and render different view files for example.

### Using wireRenderFile

There's nothing special when using wireRenderFile, just save it to a variable.

```php
// contact.php
$view->bodyHtml = wireRenderFile("./newsletter_body", array('title' => $page->title, 'body' => $page->body));
```

### Other stuff

See the official Latte documentation for more stuff like

- setting variables
- setting default values for variables
- capturing html section into variable
- switching latte syntax (eg. to double curly braces for script tags)
- etc.

### Global variables

You can set global variables in _init.php because it will be always loaded (provided if you load it in your config.php "prependTemplateFile").

Example: setting $contactPage global variable
```php
// _init.php
$view->contactPage = $pages->get(1044);
```


### Filters

You can make your own filters by adding new items to the "$view->_filters" array.

*Example: add "activeClass" filter:*

```php
$view->_filters['activeClass'] = function ($currentPage) {
    $page = wire('page');

    return ($page == $currentPage || $page->parentsUntil(1)->has($currentPage)) ? 'active' : '';
};
```

*Usage in view file:*

```php
<body class="{$page|activeClass}">
```

Every filter gets the preceding variable as the first parameter. In this case, the parameter "$currentPage" is "$page", because the filter was called on "$page" ("$page|activeClass").

To specify parameters use the ":" character:

```php
<body class="{$page->title|truncate:20}">
```

This will truncate the title at 20 characters ("truncate" is a default filter, [see all here](https://latte.nette.org/en/filters)).

**Where to place filters?**

The best place for filters is _init.php, or in another file that ProcessWire always loads.


### Multi-language strings

You'll need to use a workaround here to be able to use multilanguage strings that will be translatable in ProcessWire's language translator.

First, create a global $t variable in _init.php:

```php
$view->t = function () {
    return t(func_get_args());
};
```

Second, create a t() function in _func.php:

```php
function t($args = null) {

    $context = "General";
    $textdomain = "/site/templates/_strings.php";

    if (!is_array($args)) {
        $args = func_get_args();
    }

    $text = isset($args[0]) ? $args[0] : "";
    $context = isset($args[1]) ? $args[1] : $context;
    $textdomain = isset($args[2]) ? $args[2] : $textdomain;

    return _x($text, $context, $textdomain);
}
```

Third, create a _strings.php in "/site/templates" (the starting comment tag is intentional):

```php
/*!
_x('Read more', 'General');
_x('Please select', 'Form');
```

These strings will be available in ProcessWire's translator.
The first parameter passed to the _x() function is the string, the second is the context.

Usage in view files:

```php
<a href="{$page->url}">{$t('Read more')}</a>
<p>{$t('Please select', 'Form')}</p>
```

Note that in the first example the context is not set because the t() function have the "General" context by default.


## PhpStorm

PhpStorm has Nette and Latte plugins, but unfortunately the Latte plugin breaks the HTML autocomplete feature in Latte files. This can be solved by setting the “.latte” extension to be perceived as a Smarty file (see “File Types” in the Settings).

![PhpStorm Latte file type](./images/phpstorm-latte-file-type.png)

Additionally, add Latte tag attributes to HTML Inspections to make them available in autocomplete.

![PhpStorm Latte attributes](./images/phpstorm-latte-attributes.png)

I have the following attributes added though the list is probably not complete:

```
n:block,n:if,n:foreach,n:class,n:syntax,n:inner-foreach,n:tag-if,n:href,n:name,n:ifset
```

With these settings PhpStorm will not mark Latte attributes as errors.


## Module settings

### API variable

This is where you can set another name for the $view object.

### Default directory for views

Directory name for .latte" files (relative to site/templates).

### Default layout file

The base layout file that all views will use.

### Clear cache

If checked, Latte cache will be cleared on saving the module.


