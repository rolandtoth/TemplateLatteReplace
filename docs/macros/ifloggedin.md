# [Macro] ifloggedin

A simple if macro to check if the current user is logged in.

```php
<p n:iflogggedin>
    If you see this you are logged in.
</p>
```

```php
<p>
{ifloggedin}
    Hello bro.
{else}
    Hello, guest bro!
{/ifloggedin}
</p>
```