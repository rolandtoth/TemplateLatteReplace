# [Filter] getline, getlines

The getlines filter explodes lines of a textarea field into an array, optionally with a filter to retrieve specific items only.

If a separator is present in the source data then an associative array is returned. Default separator is `=` which can be modified (see below).

Empty lines and lines starting with "//" will be skipped.

The filter always returns an array (even if empty) so use a foreach to display data, or use `first` or `last` filters. From v049 you can use the `getline` filter instead to retrieve only one line.

_Example 1: simple array_

Textarea field content:

```txt
Maria
John
// Peter (will be skipped)
Benedict
```

```php
<p n:foreach="($my_field|getlines) as $value">
    {$value}
</p>
```

_Example 2: associative array_

Textarea field content:

```txt
sitename = My Site
phone = 123456789
// facebook_id = 54546464646 (will be skipped)
```

```php
<p n:foreach="($my_field|getlines) as $key => $value">
    {$key}: {$value}
</p>
```

Use comma-separated values to add filters. Only matching items will be retrieved:

```php
<p n:foreach="($my_field|getlines:'facebook,linkedin') as $social">
    {$social}
</p>
```

Use `$my_field|getlines:'',':'` to change the separator to `:` (or anything else).

**Multilanguage support**

Multilanguage is supported via InputfieldTextareaLanguage. If you've set the field's behavior to inherit values from default language if it's empty on the given language, data will be available for all languages.

However, if the field on a non-default language is not empty, the missing lines won't be inherited. In such cases you will have to supply an array (pageID, fieldname) instead of field data (available from v0.4.7) to ensure inheritance.

```php
<p n:foreach="(array(1, 'settings')|getlines:'facebook,linkedin') as $social">
    {$social}
</p>
```

The example above will use facebook and linkedin values from the "default" language of the "settings" textarea if they were not found in the current language (using the "Home" page, id 1). This means that you can set common data in the "default" language, and add overrides to other languages, no need to duplicate everything in each language tab.

Tip: for site-wide settings set up a variable in ready.php and use it in view files like this:

```php
// ready.php - save the Home page's 'settings' textarea field content to a $settings variable
$view->settings = $pages->get(1)->settings;
```

```php
<p n:foreach="$settings|getlines:'facebook,linkedin') as $social">
    {$social}
</p>
```

Filter `getline` works the same way as getlines but returns a string instead an array:

```php
<html lang="{$settings|getline:'locale'}">
```