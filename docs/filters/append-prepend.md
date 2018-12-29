# [Filter] append, prepend

Appends or prepends data. Can be used with simple strings and arrays too. Note that if you use it with associative arrays, array items may be overwritten because of `array_merge`.

This filter can be useful when chaining filters so you can modify the original data in one step.

```php
{var $arr = array(4,5,6)}
{var $arr_assoc = array('one' => 'first', 'two' => 'second', 'three' => 'third')}

{('Hello')|append:' World!'}
{*outputs: Hello World!*}

{('World!')|prepend:'Hello '}
{*outputs: Hello World!*}

{$arr|prepend:3}
{*outputs: array(3,4,5,6)*}

{$arr|append:7}
{*outputs: array(4,5,6,7)*}

{$arr|prepend:array(1,2,3)}
{*outputs: array(1,2,3,4,5,6)*}

{$arr|append:array(7,8,9)}
{*outputs: array(4,5,6,7,8,9)*}

{$arr_assoc|append:array('three' => 'new_third')|bd}
{*outputs: array('one' => 'first', 'two' => 'second', 'three' => 'new_third')*}
```