# [Filter] surround

Surround item or array of items with html tag. The angle brackets can be included or excluded around html tags.

```php
{$page->title|surround:'h2'|noescape}
{$page->title|surround:'<h2>'|noescape}
{$page->children->title()|surround:'li'|surround:'ul class="list" data-tooltip="Children of {$page->title}"'|noescape}
```