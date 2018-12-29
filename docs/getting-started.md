# Getting Started

## Install

1. Install the module - version 0.5.1 and above requires PHP 7.0 or newer

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

*Example: pass $subPages to view file and loop through it. In the view file `$view->subPages` will be available as `$subPages`*.

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

From v0.2.5 it is possible to use latte files only and remove template php files. See the "Use latte extension" feature for details.