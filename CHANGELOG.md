#Changelog


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
