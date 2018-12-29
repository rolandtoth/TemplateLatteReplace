# [Filter] protectemail

Protects email with different methods (javascript/js, javascript_charcode, hex, drupal, texy). 
Defaults to `javascript` mode.

```php
{var $email = 'info@domain.com'}

{$email|protectemail|noescape}
{$email|protectemail:'javascript'|noescape}
{$email|protectemail:'hex','Email me','class="mailto-link"'|noescape}
{$email . '?subject=The%20subject%20of%20the%20mail'|protectemail:'js','Email me','class="button"'|noescape}
```