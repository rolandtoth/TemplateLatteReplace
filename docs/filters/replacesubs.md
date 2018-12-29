## replacesubs

Allows adding placeholders in format `((item))` to fields to replace them with data coming from an array. Mainly for replacing placeholders in CKEditor content, eg. with global contact data, social links, etc.

Consider the following `settings` textarea as the replacement data source:

```txt
phone = 123456
email = abcd@domain.com
```

Now you can use `((phone))` and `((email))` placeholders in eg. a CKEditor field to replace values. For example, the `body` CKEditor HTML content may look like this:

```html
<ul>
   <li><strong>Phone</strong>: ((phone))</li>
   <li><strong>Email</strong>: <a href="mailto:((email))">((email))</a></li>
</ul>
```

Then in your latte file you can apply the filter and set the source array of replacements. In this example the replacement source is a simple textarea, from which the filter will create a replacement array:

```php
{$page->body|replacesubs:$page->settings|noescape}
```

The main advantage is that you don't need to hardcode keys into template files, unlike with `getline`/`getlines`. So eg. if you need to add another phone number, just add `phone2 = 99999` to the Settings field and the corresponding markup to the CKEditor.