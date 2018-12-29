# [Macro] ifstatus

Macro to check page status. Accepts a page ID or Page object as the first parameter, and a status as the second.

Available statuses:

- unpublished
- hidden
- locked
- system
- trash
- draft 
- public (*)
- published (*)

Statuses marked with an asterisk are not part of ProcessWire API:

- public: equivalent to `$page->isPublic()`
- published: not unbpublished + not hidden + not in trash: `!$page->hasStatus("unpublished") && !$page->hasStatus("hidden") && !$page->isTrash()`

```php
{ifstatus:1023,'hidden'}
    <p>Page 1023 status is hidden.</p>
{/ifstatus}
```

**Inverting statuses**

Statuses can be inverted by prefixing them with an exclamation mark. So `!hidden` means "not hidden", `!trash` means "not in Trash", etc.

```php
<section n:ifstatus="1023, '!unpublished'">
    <p>Page 1023 is not unpublished.</p>
</section>
```

**Multiple statuses**

You can specify multiple statuses by using an array of statuses. Only AND operation is supported.

```php
{ifstatus $servicesPage, array('!hidden', '!locked')}
    <p>The services page is not hidden nor locked.</p>
{else}
    <p>The services page is locked or hidden.</p>
{/ifstatus}
```