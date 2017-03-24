#Changelog


### 0.3.9 (2017-03-24)

- 'editlink' macro updates



### 0.3.8 (2017-03-22)

- new macros: 'minify' and 'editlink'



### 0.3.7 (2017-02-27)

- added 'truncatehtml' filter



### 0.3.6 (2017-02-24)

- added 'embediframe' filter



### 0.3.5 (2017-02-23)

- added 'protectemail' filter



### 0.3.4 (2017-02-20)

- added pagination classes to 'bodyclass' filter



### 0.3.3 (2017-02-17)

- added 'surround' and 'lazy' filters
- documentation move do GitHub wiki



### 0.3.2 (2017-02-16)

- multilanguage fix for 'getsetting' filter
- documentation fix for 'n:pages'



### 0.3.1 (2017-02-15)

- added 'default' filter
- added 'bgimage' filter
- added 'consolelog' filter
- added 'bd, bdl, dump, d' filters (requires Tracy Debugger module)



### 0.3.0 (2017-02-10)

- added 'sanitize' filter (+ alias 'sanitizer')
- added alias 'pager' to 'renderpager' filter
- fix $modules fuel instead $module



### 0.2.9 (2017-01-31)

- change in adding custom macros and filters: $view->addMacro(), $view->addFilter()
- added $view->invokeFilter($name, $args) method to run filters directly in PHP
- updated filters to accept PageArrays instead selectors only
- new filter: 'renderpager'
- new filter: 'getsetting' (to use with TextformatterMultivalue module)
- $view->latte returns the Latte object
- lowercase filter names



### 0.2.8 (2017-01-26)

- added "defaultTextdomain" setting to module config
- update Latte to 2.4.3



### 0.2.7 (2017-01-20)

- added "breadcrumb" and "count" filters
- "get" filter returns the first image in case of Pageimages



### 0.2.6 (2017-01-17)

- added "getParent" and "get" filters



### 0.2.5 (2017-01-10)

- option to replace PHP templates with Latte files (needs manual renaming admin.php to admin.latte)
- removed automatic views directory creation feature



### 0.2.4 (2016-12-06)

- added additional filters and macros (optional, see README)
- added 'languages', 'fields', 'templates', 'logs' to the default API variables (suggested by Pixrael)



### 0.2.3 (2016-11-22)

- Latte upgraded to 2.4.2
- do not use autoloader for loading Latte (fixes FileCompiler issues)



### 0.2.2 (2016-11-15)

- multiple macros fix



### 0.2.1 (2016-10-07)

- do not process HannaCode renders



### 0.2.0 (2016-09-27)

- use absolute path for default layout file



### 0.1.9 (2016-07-25)

- added htmlspecialchars_decode for the global _t() translation helper 



### 0.1.8 (2016-06-30)

- Latte updated to 2.4
- removed FakePresenter because there's built-in way to set default layout file in v2.4



### 0.1.7 (2016-06-17)

- run translated strings through html_entity_decode() (suggested by adrianmak)



### 0.1.6 (2016-06-08)

- enable setting template latte file with/without extension ($view->viewFile = 'basic-page' and $view->viewFile = 'basic-page.latte')
- $view->json_encode returns JSON encoded string/markup. Possible values are true and PHP's json_encode options (eg. JSON_PRETTY_PRINT).
- if "viewFile" begins with "//", $config->paths->templates and viewDir is not prepended to the view path



### 0.1.5 (2016-05-18)

- removed default context "General" to allow _'string' syntax



### 0.1.4 (2016-05-07)

- fix hardcoded 'view' for api_var when setting fuel



### 0.1.3 (2016-05-04)

- replace t() and n() helper functions with _t() and _p() to avoid possible collisions
- add replacement, context and textdomain support for pluralization helper function



### 0.1.2 (2016-05-02)

- add global t() and n() helper functions for easier string translation
- documentation fixes



### 0.1.0 (2016-05-01)

- first public release
