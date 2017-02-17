<?php namespace ProcessWire;

$view = $this->wire($this->api_var);


// return a default value if empty or falsy value passed
// {$page->product_description|default:'No description is available for this product.'}
$view->addFilter('default', function ($str = '', $default = '') {
    return strlen($str) > 0 ? $str : $default;
});


$view->addFilter('activeclass', function ($currentPage, $className = 'active') {
    $page = $this->wire('page');

    return ($page == $currentPage || $page->parentsUntil(1)->has($currentPage)) ? $className : '';
});


// add various classes to <body>, eg id, template, language, home
$view->addFilter('bodyclass', function ($p) {

    $id = $p->id;
    $class = "";

    if (!empty($id)) {

        $class = array();

        $class[] = ($id == 1) ? "home" : "page-" . $id;

        if (!in_array($p->parent->id, array(0, 1)))
            $class[] = "parent-" . $p->parent->id;


        if ($this->wire('user')->language)
            $class[] = "lang-" . $this->wire('user')->language->name;

        $class[] = "template-" . $p->template->name;

        $class = implode(" ", $class);
    }

    return $class;
});


// returns a selector built from IDs (eg. "id=1045|1033|1020")
$view->addFilter('getselector', function ($pArr = null) {

    if (is_null($pArr) || !($pArr instanceof PageArray))
        return false;

    return 'id=' . $pArr->id('|');
});


$view->addFilter('getpage', function ($selector = null) {

    if (is_null($selector))
        return false;

    return $this->wire('pages')->get($selector);
});


$view->addFilter('getpages', function ($selector = null, $extraSelector = null) {

    if (is_null($selector))
        return false;

    if ($selector instanceof PageArray)
        return $selector->filter($extraSelector);

    if (!is_null($extraSelector))
        $selector .= ',' . $extraSelector;

    return $this->wire('pages')->find($selector);
});


$view->addFilter('renderpager', function ($pArr = null, $options = null) {

    if (is_null($pArr) || !($pArr instanceof PageArray))
        return false;

    $paginationSettings = array(
        'numPageLinks' => 10, // Default: 10
        'getVars' => null, // Default: empty array
        'baseUrl' => array(), // Default: empty
        'listMarkup' => "<ul class='pagination'>{out}</ul>",
        'itemMarkup' => "<li class='{class}'>{out}</li>",
        'linkMarkup' => "<a href='{url}'><span>{out}</span></a>",
        'nextItemLabel' => '→',
        'previousItemLabel' => '←',
        'separatorItemLabel' => '',
        'separatorItemClass' => '',
        'nextItemClass' => 'next',
        'previousItemClass' => 'previous',
        'lastItemClass' => 'last',
        'currentItemClass' => 'active'
    );

    if (!is_null($options)) {
        if (is_array($options)) {   // merge user options
            $paginationSettings = array_merge($paginationSettings, $options);
        } elseif (is_numeric($options)) {   // only a number is passed, set numPageLinks to it
            $paginationSettings['numPageLinks'] = $options;
        }
    }

    return $pArr->renderPager($paginationSettings);
});


// alias to "renderpager"
$view->addFilter('pager', function () use ($view) {
    return $view->invokeFilter('renderpager', func_get_args());
});


$view->addFilter('breadcrumb', function ($p = null, $args = null) {

    if (is_null($p))
        return false;

    if (!is_array($args))
        $args = array($args);

    $markup = '';

    $root = isset($args['root']) ? $args['root'] : 1;
    $addHome = isset($args['addHome']) ? $args['addHome'] : true;
    $addCurrent = isset($args['addCurrent']) ? $args['addCurrent'] : false;
    $addCurrentLink = isset($args['addCurrentLink']) ? $args['addCurrentLink'] : false;
    $class = isset($args['class']) ? $args['class'] : 'breadcrumb';
    $id = isset($args['id']) ? $args['id'] : '';
    $addAttributes = isset($args['addAttributes']) ? true : false;

    if (strlen($id))
        $id = ' id="' . $id . '"';

    if (strlen($class))
        $class = ' class="' . $class . '"';

    if ($root instanceof Page)
        $root = $root->id;

    // return if current page is not below root
    if ($this->wire('pages')->get($root)->find($p->id)->count() == 0)
        return false;

    if ($addHome)
        $parents = $this->wire('pages')->get($root)->and($p->parentsUntil($root));
    else
        $parents = $p->parentsUntil($root);

    // do not attempt to display breadcrumb if there are no items
    if ($parents->count() == 0)
        return false;

    $getAttributes = function ($str) use ($addAttributes) {
        if ($addAttributes) {
            return ' data-page="' . $str . '" ';
        }
    };

    foreach ($parents as $parent) {
        $markup .= '<li' . $getAttributes($parent->id) . '><a href="' . $parent->url . '">' . $parent->title . '</a></li>';
    }

    if ($addCurrent) {
        $markup .= '<li' . $getAttributes($p->id) . '>';
        $markup .= $addCurrentLink ? '<a href="' . $p->url . '">' . $p->title . '</a>' : '<span>' . $p->title . '</span>';
        $markup .= '</li>';
    }

    return '<ul' . $id . $class . '>' . $markup . '</ul>';
});


// get page field
// use getParent if PageArray is passed
$view->addFilter('get', function ($selector = null, $field = 'title') {

    if (is_null($selector))
        return false;

    // needed for $pageArray|getParent|get to work
    if ($selector instanceOf Page)
        $selector = $selector->id;

    $page = $this->wire('pages')->get($selector);

    if (!$page->id)
        return false;

    if ($page->$field instanceof Pageimages)
//    if (is_array($page->$field))
        $value = $page->$field->first();
    else
        $value = $page->$field;

    return $value;
});

/**
 * Lazysizes helper filter
 * Requires lazysizes.js added manually and adding the "lazyload" class to img
 * https://github.com/aFarkas/lazysizes
 *
 * <img src="{$page->images->first()->width(900)|lazy|noescape}" alt="" class="lazyload" />
 * <img src="{$page->images->first()->width(900)|lazy:3|noescape}" alt="" class="lazyload" />
 */
$view->addFilter('lazy', function ($img = null, $divisor = 4) {

    $divisor = (int)$divisor;

    if (is_null($img) || !($img instanceof Pageimage) || $divisor <= 1)
        return false;

    // get width and height pixel values from the current resized image and resize the original
    $imgSmall = $img->getOriginal()->size($img->width/$divisor, $img->height/$divisor, array('quality' => 70));

    return $imgSmall->url . '" data-src="' . $img->url;
});


// count PageArray
$view->addFilter('count', function ($selector = null) {

    if (is_null($selector))
        return false;

    return ($selector instanceof PageArray) ? $selector->count() : $this->wire('pages')->find($selector)->count();
});


// get parent
$view->addFilter('getparent', function ($selector = null) {

    if (is_null($selector))
        return false;

    return ($selector instanceof PageArray) ? $selector->first()->parent() : $this->wire('pages')->get($selector)->parent();
});


// inline background-image
$view->addFilter('bgimage', function ($img = null) {

    if (is_null($img))
        return false;

    return 'style="background-image: url(\'' . $img->url . '\')"';
});


// barDump (needs TracyDebugger module)
$view->addFilter('bd', function ($data = null) {
    if (!is_null($data) && function_exists('bd')) bd($data);
});

// barDump long (needs TracyDebugger module)
$view->addFilter('bdl', function ($data = null) {
    if (!is_null($data) && function_exists('bdl')) bdl($data);
});

// dump (needs TracyDebugger module)
$view->addFilter('d', function ($data = null) {
    if (!is_null($data) && function_exists('d')) d($data);
});


// alias to "d"
$view->addFilter('dump', function () use ($view) {
    return $view->invokeFilter('d', func_get_args());
});


// write to console log
$view->addFilter('consolelog', function ($data = null) {

    if (!is_null($data)) {

        if (is_array($data))
            $data = implode(',', $data);

        echo '<script>console.log("' . $data . '");</script>';
    }
});


// remove everything but numbers
$view->addFilter('onlynumbers', function ($str) {
    return preg_replace("/[^0-9]/", "", $str);
});


/**
 * Remove http, www or ending / from links
 *
 * @param string $url
 * @param string $remove
 * @return mixed|string
 */
$view->addFilter('niceurl', function ($url = null, $remove = 'httpwww/') {

    if (is_null($url))
        return false;

    $url = trim($url);

    if (strpos($remove, 'www') !== false)
        $url = str_replace('www.', '', $url);

    if (strpos($remove, 'http') !== false)
        $url = str_replace(array('https://', 'http://'), '', $url);

    if (strpos($remove, '/') !== false)
        $url = rtrim($url, '/');

    return $url;
});


// helper filter for TextformatterMultiValue module
$view->addFilter('getsetting', function ($args = null) use ($view) {

    $isMultiLang = is_object(wire('languages'));
    $originalLang = 'default';
    $user = wire('user');

    if ($isMultiLang) {
        $originalLang = $user->language->name;
    }

    if (!is_array($args)) {
        $args = func_get_args();
    }

    if (isset($args[1])) {
        $key = $args[1];
    } else {
        return false;
    }

    $p = (isset($args[0]) && !empty($args[0])) ? $args[0] : wire('pages')->get(1);
    $language = isset($args[2]) ? $args[2] : $originalLang;
    $recursive = isset($args[3]) ? $args[3] : true;


    // allow only page ID to be passed
    if (is_numeric($p)) {
        $p = wire('pages')->get($p);
    }

    if ($isMultiLang) {
        $user->language = $language;
    }

    $result = (isset($p->mv) && !empty($p->mv->$key->value)) ? $p->mv->$key->value : null;

    // try default language if value not found
    if ($isMultiLang && is_null($result) && $recursive) {
        $result = $view->invokeFilter('getsetting', array($p, $key, 'default', false));
    }

    // return empty string if it's literally set to NULL
    if (trim($result) === 'NULL') {
        $result = '';
    }

    if ($isMultiLang) {
        $user->language = $originalLang;
    }

    return $result;
});


/**
 * Surround item or array of items with html tag
 *
 * {$page->title|surround:'h2'|noescape}
 * {$page->children->title()|surround:'li'|surround:'ul class="list" data-tooltip="Children of {$page->title}"'|noescape}
 */
$view->addFilter('surround', function ($data = null, $startTag = null) {

    if (is_null($data) || is_null($startTag)) return false;
    if (!is_array($data)) $data = array($data);

    if (strpos($startTag, ' ') !== false) {
        $arr = explode(' ', $startTag, 2);
        $endTag = $arr[0];
    } else {
        $endTag = $startTag;
    }

    $startTag = '<' . $startTag . '>';
    $endTag = '</' . $endTag . '>';

    $markup = implode($endTag . $startTag, $data);

    return $startTag . $markup . $endTag;
});


/**
 * Return sanitized value using ProcessWire's sanitizer
 *
 * {('Lorem ipsum')|sanitize:'fieldName'}
 * {$p->body|sanitize:'text', array('multiLine' => true, 'stripTags' => false)}
 * {('2017/02/28')|sanitize:'date', 'YYY F j.', array('returnFormat' => 'Y. F j.')}
 */
$view->addFilter('sanitize', function ($value = null, $fx = null, $options = null) {

    if (is_null($value) || is_null($fx))
        return false;

    if (is_null($options)) {
        $out = wire('sanitizer')->$fx($value);

    } else {

        $args = func_get_args();

        unset($args[1]); // remove $fx
        $args = ($args); // reindex

        $out = call_user_func_array(array(wire('sanitizer'), $fx), $args);
    }

    return $out;
});


// alias to "sanitize"
$view->addFilter('sanitizer', function () use ($view) {
    return $view->invokeFilter('sanitize', func_get_args());
});
