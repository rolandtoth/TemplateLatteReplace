# [Filter] truncatehtml

Truncates html string. Unlike the built-in [truncate](https://latte.nette.org/en/filters#toc-truncate) filter it adds closing html tags to the end.

Parameters:

- **limit**: truncate text on this character count. Default: 120. Truncating can be disabled using `false`).
- **break**: truncate before the first occurrence of the break character (after the number of characters set in limit). Default: ' '.
- **pad**: character(s) to add at the end of truncated text (inside html tags). Default: '…' (ellipsis).

Use `null` to use the default values.

```php
{*truncate html at 100 chars*}
{$page->body|truncatehtml:100|noescape}

{*truncate html at 100 chars, break on ' ' (null = default) and add ' [...]' pad*}
{$page->body|truncatehtml:100,null,' [...]'|noescape}

{*truncate html at 120 chars (null = default), and truncate before the first paragraph that occurs after 120 chars*}
{$page->body|truncatehtml:null,'<p',''|noescape}

{*disable truncating, eg. for testing*}
{$page->body|truncatehtml:false|noescape}
```