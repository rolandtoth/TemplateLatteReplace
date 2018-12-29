## bodyclass

Adds some classes to the element (preferably to the "body") reflecting the page ID, template and user language, page number (if applicable) and $view->viewFile:

- page ID: eg. "page-1032"
- template name: "template-basic-page" (or "home" in case of the homepage)
- language: "lang-default"
- pagination: "pagenum-2"
- viewFile: eg. "v-partials-featured_slider"

```php
<body class="{$page|bodyclass}">
```

You can also add custom classes using "$page->bodyclass" (string or array). Note that currently custom body classes will overwrite each other when used more than one times so you need to manually ensure to get around this, eg. creating an array and appending items).