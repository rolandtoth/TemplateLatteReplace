# [Filter] activeclass

Adds an 'active' class if the Page passed is the current page, or it is a children of the parent page (multi-level). Useful for navigation items or sliders for example. You can change the default className to another one by passing it as a parameter (added using  ":'current'" in the following snippet). Note that you need to take care of the spaces if you use another classes on the element.

```php
<ul n:inner-foreach="$menuItems as $p">
    <li class="menu-item {$p|activeclass:'current'}">
        <a href="{$p->url)}">{$p->title|noescape}</a>
    </li>
</ul>
```