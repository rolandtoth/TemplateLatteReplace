# Module Options

## API variable

This is where you can set another name for the $view object.

## Default directory for views

Directory name for .latte" files (relative to site/templates).

## Default layout file

The base layout file that all views will use.

## Default textdomain

The default textdomain file used for translations, eg. `/site/ready.php` or `/site/templates/_strings.php`.

## Options

- Load additional macros: whether to include additional macros (from v2.4)
- Load additional filters: whether to include additional filters (from v2.4)
- Use latte extension: if checked, you can remove PHP tempate files and use only Latte files (from v2.5). Note: `admin.php` must be renamed to `admin.latte` if you check this (or alternatively create `admin.latte` file next to `admin.php` and add `require('./admin.php');` to it). For PHP logic you can still use ready.php (or _init.php) and use conditionals to target specific templates (eg. `if($page->template == 'basic-page')`).
- Ignored templates: comma-separated list of templates to be ignored by Latte. Use template file names instead template labels. Defaults: `form-builder, email-administrator, email-autoresponder, HannaCode`.
- Disable `noescape` filter: removed from v0.5.9

## Clear cache

If checked, Latte cache will be cleared on saving the module.