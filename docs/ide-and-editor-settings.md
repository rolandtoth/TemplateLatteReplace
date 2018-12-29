# IDE and editor settings

## PhpStorm

PhpStorm has [Nette](https://plugins.jetbrains.com/plugin/7231-nette-framework-helpers) and [Latte](https://plugins.jetbrains.com/plugin/7457-latte) plugins, but unfortunately the Latte plugin breaks the HTML autocomplete feature in Latte files. This can be solved by setting the “.latte” extension to be perceived as a Smarty file (see “File Types” in the Settings).

Additionally, add Latte tag attributes to HTML Inspections to make them available in autocomplete.

I have the following attributes added though the list is probably not complete:

```
n:block,n:if,n:ifset,n:foreach,n:inner-foreach,n:class,n:syntax,n:tag-if,n:href,n:name,n:attr
```

With these settings PhpStorm will not mark Latte attributes as errors.


## Sublime Text 3

Instructions for Sublime Text 3 (should work on 2 too):

- Install [Nette + Latte + Neon](https://packagecontrol.io/packages/Nette%20%2B%20Latte%20%2B%20Neon) package
- Install [Smarty](https://packagecontrol.io/packages/Smarty) package
- Set **latte** extension to open as **smarty** so you can keep the HTML code autocomplete working

Bonus: install **HTML-CSS-JS Prettify** package and on its preferences add **latte** and **smarty** on allowed file extensions:

```
"html": {
    "allowed_file_extensions": ["htm", "html", "xhtml", "shtml", "xml", "svg", "latte", "smarty"],
```
**Warning**: HTML Prettify may break lines on strings translations that have more than one word, for instance:
```php
{_'A technical book', 'Books'}
```

May result in

```php
{_'A 
technical 
book', 'Books'}
```

so it won't appear translated any more. You'll have to remove the line breaks manually (for now).