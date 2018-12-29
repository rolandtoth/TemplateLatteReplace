## getpages

This is a "filter" version of the macro "pages" (see above). The difference is that you can pass an extra selector too.

```php
<p n:foreach="('parent=1088'|getpages) as $p">
    {$p->title|noescape}
</p>

{* $view->servicesPages is set in ready.php and contains "template=service,sort=-created" *}
{* now get only 6 of them *}
<p n:foreach="($servicePages|getpages:'limit=6') as $p">
    {$p->title|noescape}
</p>
```