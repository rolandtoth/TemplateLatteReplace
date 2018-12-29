# [Filter] sanitize

Applies ProcessWire's sanitizer. The alias "sanitizer" can also be used.

```php
{('Lorem ipsum')|sanitize:'fieldName'}
{$p->body|sanitize:'text', ['multiLine' => false]}
{$p->body|sanitize:'text', array('multiLine' => true, 'stripTags' => false)}
{('2017/02/28')|sanitizer:'date', 'YYY F j.', array('returnFormat' => 'Y. F j.')}
```