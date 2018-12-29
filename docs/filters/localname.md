## localname

This is a helper filter to avoid empty localnames in multilanguage setups.

If the page name for the non-default language is the same as of the default language, the built-in localName may return an empty string. The filter will return the appropriate page name in these cases.

Additionally you can pass a language name or ID to get the page name on that language.

```php
$page->localname
$page->localname:'dutch'
```