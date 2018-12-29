# [Macro] editlink

Renders a link to open the admin and edit the page passed as a parameter. The edit link will be visible only to users having the right to edit the page.

Can't be used as an n:macro.

**Parameters**:

- target: the Page to edit in the admin. If it's the first parameter then can be passed simply as `$page` instead `target => $page`
- text: text to appear on the link (default: `Edit`)
- attrs: attributes to add, eg. class, data-attributes, inline style, link target, etc
- urlparams: url parameters to append to the edit link url (string)

```php
{* all parameters with default values: target Page, text to show, attributes: CSS class, target and url parameters *}
{editlink target => $page, text => 'Edit', attrs => 'class="edit-link" target="_blank"', urlparams => '?modal=1'}

{* Edit link for $p (ProcessWire page) *}
{editlink target => $p}
{editlink $p}

{* Edit link for the page with id 1050 *}
{editlink 1050}

{* Edit link for the current $page *}
{editlink}
```

**Setting defaults**

You can set default parameters by creating a `$view->editlinkDefaults` array, eg. in `ready.php`:

```php
$view->editlinkDefaults = array(
    'text' => '#',
    'attrs' => 'class="edit-link" target="_blank"'
);
```

This way all edit links will have "Edit page" text and "edit-link" class by default and open in a new window. Setting parameters on individual edit links will override these.

*Tip: if "attrs" starts with a "+" character, then it will be appended to the default attrs set in $view->editlinkDefaults instead overwriting (eg. `{editlink 'target' => $p, 'attrs' => '+class="top-right small"'}` )*.

**Styling**

The rendered link has no styling, you (the developer) should take care of that with custom CSS. By default the macro adds a `data-editlink` attribute that you can use as a selector.

Example of adding icon-only links:

```css
[data-editlink] {
  all: initial;
  text-indent: -9999px;
  display: inline-block;
  border: 5px solid transparent;
  float: right;
  width: 20px;
  height: 20px;
  vertical-align: middle;
  opacity: 0.3;
  z-index: 200;
  background: transparent url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAB5UlEQVRYR+3XO2gVURAG4C9iqTZa2Ina2KTRSkER1MJCa2ttYqfgE/EBPhLfjY0Iae1UbCwFFUEbsRJUBG0UREWxFBMGZkOy3HPvZXfhFsmBhYU9O/8//8z5d3bMiNfYiPEtCgL7sQtfcQ8/5qteUmAr9mLVECX6g3OFfbdwFJ+wFt+xGT+r/b0ITOFkbvo1BIEIGoTr6zaO4ArOYD3e5f3NEoHteJYZXcb/IQj02lKBRwKh0E58xjdM43SJwCQOplwzLcGP4wGeImI9wQR24HmJwB3sxqYOwG9kjA14ixVZ2mv9mrANgUr2yLwCD6zrOIZTuFpPrN6ETQkMAo+mXpB5lyVoDB4k2iowCPxElqDYUm0IVCZTqvlA8DYKhKuFtV6suWDVcEOBtyFwAPexDl9S3wq8rkgl/+v0gvNdHMO72IONGMfhNJkSeGB+xKM8knMcmvbAe6xJq16Nf2ky0Rel1RmBIP0hP1Zhs3G9wN8B7tkZgYYu3W0JmpBYUmBJgVDgIcIrij4QE9GhnIiajmO9GnR5DqThE2HfRQIxXL7MTRdazITzMZblYBrfhy14049APLuUk2tMu3Pjc5Nzl++EU4Zrns3YC0KV/gu2YR9WtgCuXv2Nx3jVK9ai+DXrK+LIFZgFWbWDIS5SaWwAAAAASUVORK5CYII=') center center no-repeat;
  background-size: cover;
}

[data-editlink]:hover { opacity: 1; cursor: pointer; }
```

**Open edit link in a modal**

To achieve this you'll need a JavaScript tool and adding `urlparams` and `attrs` to the edit link.

For example this will generate a link that can be used together with [lightGallery.js](https://sachinchoolur.github.io/lightgallery.js/).

```php
{editlink $page, urlparams => '&modal=1', attrs => 'data-iframe="true"'}
```