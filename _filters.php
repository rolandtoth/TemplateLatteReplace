<?php namespace ProcessWire;

$view = $this->wire($this->api_var);

// explode string by a separator + trim + remove empty items
function stringToArray($str, $separator, $format = 'string')
{
    if (strpos($str, $separator) === false) {
        return false;
    }

    $arr = explode($separator, $str);
    $arr = array_map('trim', $arr); //trim whitespace
//    $arr = array_filter($arr, 'trim');  // remove empty items (removes 0x200 too!)

    $arr = array_filter($arr, function ($value) {
        return ($value !== null && $value !== false && $value !== '');
    });

    if ($format === 'int') {
        $arr = array_map('intval', $arr);
    }

    return $arr;
}


// calculate width-height values for an image if one dimension is 0
//function getCurrentSizePair($sizeArr, $ratio)
//{
//
//    if ($sizeArr[0] == 0) {
//        $sizeArr[0] = (int) ($sizeArr[1] * $ratio);
//
//    } elseif ($sizeArr[1] == 0) {
//        $sizeArr[1] = (int) ($sizeArr[0] / $ratio);
//    }
//
//    return $sizeArr;
//}

// Fix for empty localname
// language name or id can be passed to retrieve localname in other languages
// $page->localname:'dutch'
// https://processwire.com/talk/topic/10742-find-page-name-field-in-non-default-language/
$view->addFilter('localname', function ($p, $lang = null) {

    if (!($p instanceof Page)) {
        return false;
    }

    if (is_null($lang)) {
        $lang = wire('user')->language;
    }

    $out = $p->localName($lang);

    return strlen($out) ? $out : $p->name;
});


/**
 * srcset filter for LazySizes
 * IMPORTANT - add to CSS: img[data-sizes="auto"] { display: block; width: 100%; }
 * eg. <img src="..." data-srcset="{$page->images->first()|srcset:'540x320,*3,/2', array('upscaling' => false)|noescape}" data-sizes="auto" alt="" class="lazyload" />
 */
$view->addFilter('srcset', function ($img, $sets = null, $options = null) use ($view) {

    $srcSetString = "";
    $imgSizes = array();
    $srcSets = array();

    if (!($img instanceof Pageimage) || is_null($sets)) {
        return false;
    }

    $srcsetArray = stringToArray($sets, ',');

    if (!is_array($srcsetArray)) {
        return false;
    }

    // get sets in array format
    for ($ii = 0; $ii < count($srcsetArray); $ii++) {

        $set = $srcsetArray[$ii];
        $currentSize = false;

        if ($set === '0x0') {
            continue;
        }

        // first item must be width x height string (no multiplier nor divisor)
        if ($ii === 0) {

            // stop if it's not in WxH format
            if (strpos($set, 'x') === false) {
                // if it's only a number, assume Wx0
                if (is_numeric($set) && (int)$set > 0) {
                    $set = $set . 'x0';
                } else {
                    return false;
                }
            }

            $currentSize = stringToArray($set, 'x', 'int');

        } else {

            if (strpos($set, '/') === 0) {

                // divisor, eg. "/3" - calculate values from first item of $imgSizes
                $divisor = (double)ltrim($set, '/');

                if ($divisor > 0) {
                    $currentSize = array_map(function ($item) use ($divisor) {
                        return (int)$item / $divisor;
                    }, reset($imgSizes));
                }

            } elseif (strpos($set, '*') === 0) {

                // multiplier, eg. "*3" - calculate values from first item of $imgSizes
                $multiplier = (double)ltrim($set, '*');

                if ($multiplier > 0) {
                    $currentSize = array_map(function (&$item) use ($multiplier) {
                        return (int)$item * $multiplier;
                    }, reset($imgSizes));
                }

            } else {
                // no divisor nor multiplier
                $currentSize = stringToArray($set, 'x', 'int');
            }
        }

        if ($currentSize) {
            $imgSizes[] = $currentSize;
        }
    }

    if (empty($imgSizes)) {
        return false;
    }

    // create associative array of resized images, use widths as keys
    foreach ($imgSizes as $set) {
        $currentImage = $img->size($set[0], $set[1], $options);
        $srcSets[$currentImage->width] = $currentImage->url;
    }

    ksort($srcSets);

    // build srcset string
    foreach ($srcSets as $width => $url) {
        $srcSetString .= $url . ' ' . $width . 'w,';
    }

    $view->savetemp($srcSets);

    return rtrim($srcSetString, ',');
});


// set temporary variable and return original data
// variable can be used as gettemp() afterwards
$view->addFilter('savetemp', function ($data) use ($view) {

    $view->savetemp($data);

    return $data;
});


/**
 * Check if a Select Options field on a page has a given option selected.
 *
 * @param $p              \ProcessWire\Page
 * @param $field_with_key string field name and option id/value/title with dot notation,
 *                        eg. "page_options.3" or "page_options.hide_header" or "page_options.Hide header"
 *                        in "3=hide_header|Hide header"
 *
 * @return bool
 */
$view->addFilter('optionchecked', function ($p, $field_with_key = '') {

    if (!($p instanceof Page) || !strpos($field_with_key, '.') > 0) {
        return false;
    }

    $arr = explode('.', $field_with_key);
    $f = trim($arr[0]); // string chunk until the first dot

    if(!$p->template->hasField($f)) {
        return false;
    }

    $key = substr($field_with_key, strlen($f) + 1); // rest of the string

    $selector = is_numeric($key) ? $key : 'value|title=' . htmlentities($key);

    return $p->{$f}->has($selector);
});


// return a default value if empty or falsy value passed
// {$page->product_description|default:'No description is available for this product.'}
$view->addFilter('default', function ($str = '', $default = '') {
    return strlen($str) > 0 ? $str : $default;
});


/**
 * filter replacetokens (for CKEditor plugin Token Replacement)
 */
$view->addFilter('replacetokens', function ($data, $tokenStart = "\${", $tokenEnd = "}") {

    $config = \ProcessWire\wire('config');

    if (!isset($config->tokens) || !is_array($config->tokens)) {
        return $data;
    }

    $search = array_map(function ($item) use ($tokenStart, $tokenEnd) {
        return $tokenStart . $item . $tokenEnd;
    }, array_keys($config->tokens));

    $replace = array_values($config->tokens);

    return str_replace($search, $replace, $data);
});


$view->addFilter('activeclass', function ($currentPage, $className = 'active') {
    $p = $this->wire('page');

    return ($p == $currentPage || $p->parentsUntil(1)->has($currentPage)) ? $className : '';
});


// add various classes to <body>, eg id, template, language, home
$view->addFilter('bodyclass', function ($p) {

    $id = $p->id;
    $class = "";

    if (!empty($id)) {

        $class = array();
        $view = $this->wire($this->api_var);

        $class[] = ($id == 1) ? "home" : "page-" . $id;

        if (!in_array($p->parent->id, array(0, 1))) {
            $class[] = "parent-" . $p->parent->id;
        }

        if ($pageNum = wire('input')->pageNum) {
            if ($pageNum > 1) {
                $class[] = "pagenum-" . $pageNum;
            }
        }

        if ($this->wire('user')->language) {
            $class[] = "lang-" . $this->wire('user')->language->name;
        }

        $class[] = "template-" . $p->template->name;

        if ($p->body_class) {
            $custom_classes = (array)$p->body_class;
            $class = array_merge($class, $custom_classes);
        }

        // if there's a view file, add its file name
        if (isset($view->viewFile)) {
            $viewFile = explode('/', $view->viewFile);
            $class[] = 'v-' . $this->wire('sanitizer')->pageName(end($viewFile));
        }

        $class = implode(" ", $class);
    }

    return $class;
});


// returns a selector built from IDs (eg. "id=1045|1033|1020")
$view->addFilter('getselector', function ($pArr = null) {

    if (is_null($pArr) || !($pArr instanceof PageArray)) {
        return false;
    }

    return 'id=' . $pArr->id('|');
});


$view->addFilter('getpage', function ($selector = null) {

    if (is_null($selector)) {
        return false;
    }

    return $this->wire('pages')->get($selector);
});


$view->addFilter('getpages', function ($selector = null, $extraSelector = null) {

    if (is_null($selector)) {
        return false;
    }

    if ($selector instanceof PageArray) {
        return $selector->filter($extraSelector);
    }

    if (!is_null($extraSelector)) {
        $selector .= ',' . $extraSelector;
    }

    return $this->wire('pages')->find($selector);
});


$view->addFilter('renderpager', function ($pArr = null, $options = null) {

    if (is_null($pArr) || !($pArr instanceof PageArray)) {
        return false;
    }

    $view = $this->wire($this->api_var);

    $paginationSettings = array(
        'numPageLinks'       => 10, // Default: 10
        'getVars'            => array(), // Default: empty array
        'baseUrl'            => '', // Default: empty
        'listMarkup'         => "<ul class='pagination'>{out}</ul>",
        'itemMarkup'         => "<li class='{class}'>{out}</li>",
        'linkMarkup'         => "<a href='{url}'><span>{out}</span></a>",
        'nextItemLabel'      => '→',
        'previousItemLabel'  => '←',
        'separatorItemLabel' => '',
        'separatorItemClass' => '',
        'nextItemClass'      => 'next',
        'previousItemClass'  => 'previous',
        'lastItemClass'      => 'last',
        'currentItemClass'   => 'active'
    );

    // merge common defaults from $view->renderPagerDefaults (eg. ready.php)
    $paginationSettings = array_merge($paginationSettings,
        isset($view->renderPagerDefaults) ? $view->renderPagerDefaults : array());

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

    if (is_null($p)) {
        return false;
    }

    if (!is_array($args)) {
        $args = array($args);
    }

    $markup = '';

    $view = $this->wire($this->api_var);
    $args = array_merge($args, isset($view->breadcrumbDefaults) ? $view->breadcrumbDefaults : array());

    $root = isset($args['root']) ? $args['root'] : 1;
    $addHome = isset($args['addHome']) ? $args['addHome'] : true;
    $addCurrent = isset($args['addCurrent']) ? $args['addCurrent'] : false;
    $addCurrentLink = isset($args['addCurrentLink']) ? $args['addCurrentLink'] : false;
    $class = isset($args['class']) ? $args['class'] : 'breadcrumb';
    $id = isset($args['id']) ? $args['id'] : '';
    $addAttributes = isset($args['addAttributes']) ? true : false;

    if (strlen($id)) {
        $id = ' id="' . $id . '"';
    }

    if (strlen($class)) {
        $class = ' class="' . $class . '"';
    }

    if ($root instanceof Page) {
        $root = $root->id;
    }

    // return if current page is not below root
    if ($this->wire('pages')->get($root)->find($p->id)->count() == 0) {
        return false;
    }

    if ($addHome) {
        $parents = $this->wire('pages')->get($root)->and($p->parentsUntil($root));
    } else {
        $parents = $p->parentsUntil($root);
    }

    // do not attempt to display breadcrumb if there are no items
    if ($parents->count() == 0) {
        return false;
    }

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

    if (is_null($selector)) {
        return false;
    }

    // needed for $pageArray|getParent|get to work
    if ($selector instanceOf Page) {
        $selector = $selector->id;
    }

    $page = $this->wire('pages')->get($selector);

    if (!$page->id) {
        return false;
    }

    if ($page->$field instanceof Pageimages) //    if (is_array($page->$field))
    {
        $value = $page->$field->first();
    } else {
        $value = $page->$field;
    }

    return $value;
});


// get first item of an array
$view->addFilter('first', function ($arr = null) {
    return is_array($arr) ? reset($arr) : $arr;
});

// get first key of an array
$view->addFilter('firstkey', function ($arr = null) {
    if (!is_array($arr)) {
        return $arr;
    }
    reset($arr);

    return key($arr);
});


// get last item of an array
$view->addFilter('last', function ($arr = null) {
    return is_array($arr) ? end($arr) : $arr;
});

// get last key of an array
$view->addFilter('lastkey', function ($arr = null) {
    if (!is_array($arr)) {
        return $arr;
    }
    end($arr);

    return key($arr);
});


/**
 * Explode lines of a textarea field into an array.
 * Empty lines and lines starting with "//" are skipped.
 *
 * @param string $data      Field value
 * @param string $filter    comma separated values of keys to get
 * @param string $separator separator for associative array
 *
 * @return array
 */
$view->addFilter('getlines', function ($source = null, $filter = '', $separator = '=', $language = null) use ($view) {

    if (is_null($source)) {
        return;
    }

    $isMultiLang = is_object(wire('languages'));
    $originalLang = 'default';
    $user = wire('user');

    if ($isMultiLang) {
        $originalLang = $user->language->name;
        if (!is_null($language)) {
            $user->language = $language;
        }
    }

    // source data can be set as [$pageID, $fieldName] to allow fallback to default language
    if (is_array($source)) {
        $pid = (int)$source[0];
        $field = $source[1];
        $data = \ProcessWire\wire('pages')->get($pid)->{$field};
    } else {
        $data = $source;
    }

    $out = array();
    $comment_identifier = '//';

    $lines = trim($data);
    $lines = explode("\n", $lines);

    if (!is_array($lines)) {
        $lines = array($lines);
    }

    $lines = array_map('trim', $lines); //trim whitespace
    $lines = array_filter($lines, 'trim');  // remove empty lines

    $lines = array_values($lines);  // rearrange array keys to avoid gap


    if (strlen($filter)) {
        $filter_data = explode(',', $filter);
        $filter_data = array_map('trim', $filter_data); //trim whitespace
        $filter_data = array_filter($filter_data, 'trim');  // remove empty lines
    }

    for ($i = 0; $i < count($lines); $i++) {
        $line = $lines[$i];
        $key = $i;

        // skip items commented out
        if (strpos(trim($line), $comment_identifier) === 0) {
            continue;
        }

        if (strpos($line, $separator) !== false) {
            $arr = explode($separator, $line, 2);
            $arr = array_map('trim', $arr);
            $key = $arr[0];
            if (isset($filter_data) && !in_array($key, (array)$filter_data)) {
                continue;
            }
            $line = $arr[1];
        }

        $out[$key] = $line;
    }

    $array_items = count($out);

    if ($array_items === 1) {
        // return only the value if the array has only 1 item
        // enables inline usage without foreach
//        $out = reset($out);

    } elseif ($array_items === 0) {
        // empty array (possibly nonexisting filter or no matching line in current language)
        // try default language
        if ($isMultiLang && $language !== 'default') {
            return $view->invokeFilter('getlines', array($source, $filter, $separator, 'default'));
        } else {
//            $lines = '';
            $lines = array();
        }
    }

    if ($isMultiLang) {
        $user->language = $originalLang;
    }

    return !empty($out) ? $out : $lines;
});


$view->addFilter('getline', function () use ($view) {
    $out = $view->invokeFilter('getlines', func_get_args());

    return is_array($out) ? reset($out) : $out;
});


/**
 * Adds width, height and alt attributes to an image.
 *
 * @param string $except attributes to skip (eg. "-alt")
 */
$view->addFilter('imageattrs', function ($img, $except = null) {

    $alt = ' alt="' . $img->description . '" ';

    if (!is_null($except)) {
        if (strpos($except, '-alt') !== false) {
            $alt = '';
        }
    }

    return 'width="' . $img->width . '" height="' . $img->height . '"' . $alt;
});


/**
 * List array items with a separator
 * Similar to the built-in "implode" filter but accepts string too
 * {array($page->title, ($page->modified|date:'%Y'), $page->name)|list:'|','span'|noescape}
 *
 */
$view->addFilter('list', function ($data, $separator = '', $tag = null) {

    $out = $startTag = $endTag = '';

    if (!is_null($tag)) {
        $startTag = '<' . $tag . '>';
        $endTag = '</' . $tag . '>';
    }

    $data = is_array($data) ? $data : array($data);
    $data = array_filter($data);    // skip empty items

    foreach ($data as $item) {
        $out .= $startTag . trim($item) . $endTag . $separator;
    }

    $out = rtrim($out, $separator);     // remove last separator

    return $out;
});


/**
 * LazySizes helper filter
 * Requires lazysizes.js added manually and adding the "lazyload" class to img
 * https://github.com/aFarkas/lazysizes
 *
 * <img src="{$page->images->first()->width(900)|lazy|noescape}" alt="" class="lazyload" />
 * <img src="{$page->images->first()->width(900)|lazy:3|noescape}" alt="" class="lazyload" />
 *
 */
$view->addFilter('lazy', function ($img = null, $divisor = null, $type = 'img', $crop_preset = null) {

    $divisor = is_null($divisor) ? 4 : (int)$divisor;

    if (is_null($img) || !($img instanceof Pageimage) || $divisor <= 1) {
        return false;
    }

    $markup = '';

    // get width and height pixel values from the current resized image and resize the original
    $imgSmall = $img->getOriginal()->size($img->width / $divisor, $img->height / $divisor, array('quality' => 70));

    if ($type === 'img') {
        $markup = $imgSmall->url . '" data-src="' . $img->url;
    } elseif ($type === 'bg') {
        $markup = 'style="background-image: url(\'' . $imgSmall->url . '\')' . '" data-bgset="' . $img->url . '"';
    }

    return $markup;
});


/**
 * LazySizes helper filter for bgset plugin
 * https://github.com/aFarkas/lazysizes/tree/gh-pages/plugins/bgset
 *
 * Requires lazysizes.js and ls.bgset.js added manually and adding the "lazyload" class to the element
 *
 * <div {$page->images->first()->width(900)|bgset|noescape}" alt="" class="lazyload"></div>
 * <div {$page->images->first()->width(900)|bgset:4|noescape}" alt="" class="lazyload"></div>
 * */
$view->addFilter('bgset', function () use ($view) {
    $args = func_get_args();
    if (!isset($args[1])) {
        $args[1] = null;
    }
    $args[] = 'bg'; // ensure there's a second parameter

    return $view->invokeFilter('lazy', $args);
});


// count PageArray
$view->addFilter('count', function ($selector = null) {

    if (is_null($selector)) {
        return false;
    }

    return ($selector instanceof PageArray) ? $selector->count() : $this->wire('pages')->find($selector)->count();
});


/**
 * Generates markup for responsive iframe embed
 *
 * @param string $url the embed url
 * @param array $args options
 *
 * @return string
 */
$view->addFilter('embediframe', function ($url, $args = null) {

    if (strlen($url) == 0) {
        return false;
    }

    $view = $this->wire($this->api_var);

    $defaults = array(
        'width'     => 560,
        'height'    => 315,
        'upscale'   => true,
        'attr'      => '',
        'wrapAttr'  => 'class="embed-wrap"',
        'srcAttr'   => 'src',
        'urlParams' => ''
    );

    $defaults = array_merge($defaults, isset($view->embediframeDefaults) ? $view->embediframeDefaults : array());

    $args = is_array($args) ? array_merge($defaults, $args) : $defaults;

    extract($args);

    $width = !is_integer($width) ? 560 : $width;
    $height = !is_integer($height) ? 315 : $height;

    if ($upscale === false) {
        $wrapAttr .= ' style="max-width:' . $width . 'px;" ';
    }

    $ratio = round($height / $width * 100, 2);

    return <<< HTML
    <div $wrapAttr><div style="padding-bottom:$ratio%;"><iframe width="$width" height="$height" $srcAttr="$url$urlParams" $attr></iframe></div></div>
HTML;
});


// get parent
$view->addFilter('getparent', function ($selector = null) {

    if (is_null($selector)) {
        return false;
    }

    return ($selector instanceof PageArray) ? $selector->first()->parent() : $this->wire('pages')->get($selector)->parent();
});

// append data
$view->addFilter('append', function ($data = null, $newdata = null) {

    if (is_null($data) || is_null($newdata)) {
        return false;
    }

    if (is_array($data)) {
        if (is_array($newdata)) {
            $data = array_merge($data, $newdata);
        } else {
            $data[] = $newdata;
        }
    } else {
        $data = $data . $newdata;
    }

    return $data;
});


// prepend data
$view->addFilter('prepend', function ($data = null, $newdata = null) {

    if (is_null($data) || is_null($newdata)) {
        return false;
    }

    if (is_array($data)) {
        if (is_array($newdata)) {
            $data = array_merge($newdata, $data);
        } else {
            array_unshift($data, $newdata);
        }
    } else {
        $data = $newdata . $data;
    }

    return $data;
});


// inline background-image
$view->addFilter('bgimage', function ($img = null) {

    if (is_null($img)) {
        return false;
    }

    return 'style="background-image: url(\'' . $img->url . '\')"';
});


$view->addFilter('contains', function ($str = '', $search = '') {
    return strpos($str, $search) !== false;
});


// Create group from PageArray based on $page field.
// returns an array of key (sanitized field value), title (field value) and items (array of pages)
$view->addFilter('group', function ($pages, $fieldname = null) {

    if (is_null($fieldname) || !($pages instanceof \ProcessWire\PageArray)) {
        return $pages;
    }

    $group = array();

    for ($ii = 0; $ii < $pages->count(); $ii++) {

        $p = $pages[$ii];

        if ($p->template->hasField($fieldname)) {

            $key = \ProcessWire\wire('sanitizer')->pageNameTranslate($p->{$fieldname});

            if (!isset($group[$key])) {

                $g = new \stdClass();

                $g->key = $key;

                $titleField = $p->{$fieldname};

                if ($titleField instanceof \ProcessWire\SelectableOptionArray) {
                    $g->title = $titleField->title;
                } else {
                    $g->title = $titleField;
                }

                $g->items = new \ProcessWire\PageArray;

                $group[$key] = $g;
            }

            $group[$key]->items->add($p);
        }
    }

    return $group;
});


//https://www.labnol.org/internet/light-youtube-embeds/27941/
$view->addFilter('embedyoutube', function ($url) {
    $videoID = get_youtube_id_from_url($url);

    return '<div class="youtube-player" data-id="' . $videoID . '"></div>';
});

// barDump (needs TracyDebugger module)
$view->addFilter('bd', function ($data = null) {
    if (!is_null($data) && function_exists('bd')) {
        bd($data);
    }
});

// barDump long (needs TracyDebugger module)
$view->addFilter('bdl', function ($data = null) {
    if (!is_null($data) && function_exists('bdl')) {
        bdl($data);
    }
});

// dump (needs TracyDebugger module)
$view->addFilter('d', function ($data = null) {
    if (!is_null($data) && function_exists('d')) {
        d($data);
    }
});


// alias to "d"
$view->addFilter('dump', function () use ($view) {
    return $view->invokeFilter('d', func_get_args());
});


// write to console log
$view->addFilter('consolelog', function ($data = null) {

    if (!is_null($data)) {

        if (is_array($data)) {
            $data = implode(',', $data);
        }

        echo '<script>console.log("' . $data . '");</script>';
    }
});


// remove everything but numbers
$view->addFilter('onlynumbers', function ($str) {
    return preg_replace("/[^0-9]/", "", $str);
});


/**
 * Protect email
 * based on https://github.com/minetro/latte-email
 *
 * Modes: javascript, javascript_charcode, hex, drupal, texy
 *
 * {$email|protectemail|noescape}
 * {$email|protectemail:'javascript'|noescape}
 * {$email|v:'hex','Email me','class="mailto-link"'|noescape}
 * {$email . '?subject=The%20subject%20of%20the%20mail'|protectemail:'js','Email me','class="button"'|noescape}
 */
$view->addFilter('protectemail', function ($address, $encode = 'javascript', $text = null, $extra = null) {

    $_text = $text == null ? $address : $text;
    $_extra = $extra == null ? '' : ' ' . $extra;

    if ($encode == 'javascript' || $encode == 'js') {

        $string = 'document.write(\'<a href="mailto:' . $address . '"' . $_extra . '>' . $_text . '</a>\');';

        $js_encode = '';

        for ($x = 0, $_length = strlen($string); $x < $_length; $x++) {
            $js_encode .= '%' . bin2hex($string[$x]);
        }

        return '<script>eval(decodeURIComponent(\'' . $js_encode . '\'))</script>';

    } elseif ($encode == 'javascript_charcode') {

        $string = '<a href="mailto:' . $address . '"' . $_extra . '>' . $_text . '</a>';

        for ($x = 0, $y = strlen($string); $x < $y; $x++) {
            $ord[] = ord($string[$x]);
        }

        return "<script>{document.write(String.fromCharCode(" . implode(',', $ord) . "))}</script>";

    } elseif ($encode == 'hex') {

        preg_match('!^(.*)(\?.*)$!', $address, $match);

        if (!empty($match[2])) {
            trigger_error("mailto: hex encoding does not work with extra attributes. Try javascript.", E_USER_WARNING);

            return false;
        }

        $address_encode = '';
        for ($x = 0, $_length = strlen($address); $x < $_length; $x++) {
            if (preg_match('!\w!u', $address[$x])) {
                $address_encode .= '%' . bin2hex($address[$x]);
            } else {
                $address_encode .= $address[$x];
            }
        }

        $text_encode = '';
        for ($x = 0, $_length = strlen($_text); $x < $_length; $x++) {
            $text_encode .= '&#x' . bin2hex($_text[$x]) . ';';
        }

        $mailto = "&#109;&#97;&#105;&#108;&#116;&#111;&#58;";

        return '<a href="' . $mailto . $address_encode . '"' . $_extra . '>' . $text_encode . '</a>';

    } else {
        if ($encode == 'drupal') {

            $address = str_replace('@', '[at]', $address);
            $_text = $text == null ? $address : $_text;

            return '<a href="mailto:' . $address . '"' . $_extra . '>' . $_text . '</a>';

        } else {
            if ($encode == 'texy') {
                $address = str_replace('@', '<!-- ANTISPAM -->&#64;<!-- /ANTISPAM -->', $address);
                $_text = $text == null ? $address : $_text;

                return '<a href="mailto:' . $address . '"' . $_extra . '>' . $_text . '</a>';
            }
        }
    }

    // no encoding
    return '<a href="mailto:' . $address . '"' . $_extra . '>' . $_text . '</a>';
});


/**
 * Remove http, www or ending / from links
 *
 * @param string $url
 * @param string $remove
 *
 * @return mixed|string
 */
$view->addFilter('niceurl', function ($url = null, $remove = 'httpwww/') {

    if (is_null($url)) {
        return false;
    }

    $url = trim($url);

    if (strpos($remove, 'www') !== false) {
        $url = str_replace('www.', '', $url);
    }

    if (strpos($remove, 'http') !== false) {
        $url = str_replace(array('https://', 'http://'), '', $url);
    }

    if (strpos($remove, '/') !== false) {
        $url = rtrim($url, '/');
    }

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
 * Return sanitized value using ProcessWire's sanitizer
 *
 * {('Lorem ipsum')|sanitize:'fieldName'}
 * {$p->body|sanitize:'text', array('multiLine' => true, 'stripTags' => false)}
 * {('2017/02/28')|sanitize:'date', 'YYY F j.', array('returnFormat' => 'Y. F j.')}
 */
$view->addFilter('sanitize', function ($value = null, $fx = null, $options = null) {

    if (is_null($value) || is_null($fx)) {
        return false;
    }

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


// truncate html
$view->addFilter('truncatehtml', function () {
    return call_user_func_array(array('ProcessWire\Text', 'truncateHtmlText'), func_get_args());
});


/**
 * Surround item or array of items with html tag
 *
 * {$page->title|surround:'h2'|noescape}
 * {$page->title|surround:'<h2>'|noescape}
 * {$page->children->title()|surround:'li'|surround:'ul class="list" data-tooltip="Children of {$page->title}"'|noescape}
 */
$view->addFilter('surround', function ($data = null, $startTag = null) {

    if (is_null($data) || is_null($startTag)) {
        return false;
    }
    if (!is_array($data)) {
        $data = array($data);
    }

    // strip start and end angle brackets
    $startTag = trim($startTag, '<>');

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
 * Get embed URL from video url (supported: Youtube and Vimeo).
 *
 * @param string $url
 *
 * @return string Youtube or Vimeo embed url or FALSE if url is not supported.
 */
$view->addFilter('getembedurl', function ($url) {

    $embed_url = false;
    $youtube_embed_base = 'https://www.youtube.com/embed/';
    $vimeo_embed_base = 'https://player.vimeo.com/video/';

    if (strpos($url, 'youtube') > 0) {
        $embed_url = $youtube_embed_base . get_youtube_id_from_url($url);

    } elseif (strpos($url, 'vimeo') > 0) {
        $embed_url = $vimeo_embed_base . get_vimeo_id_form_url($url);
    }

    return $embed_url;
});


/**
 * Get Vimeo video ID from URL.
 *
 * @param string $url
 *
 * @return string Youtube video id or FALSE if none found.
 */
function get_vimeo_id_form_url($url)
{
    return substr(parse_url($url, PHP_URL_PATH), 1);
}


/**
 * Get Youtube video ID from URL.
 *
 * @param string $url
 *
 * @return string Youtube video id or FALSE if none found.
 */
function get_youtube_id_from_url($url)
{
    // return original string if doesn't seem to be an url (id only?)
    if (!isValidURL($url)) {
        return $url;
    }

    parse_str(parse_url($url, PHP_URL_QUERY), $my_array_of_vars);
    $result = $my_array_of_vars['v'];

    return $result;
}


function isValidURL($url)
{
    return filter_var($url, FILTER_VALIDATE_URL);
}

/**
 * Class Text
 * // http://www.phpsnippets.cz/latte-makro-pro-oriznuti-textu-s-html-tagy
 *
 * @author    Ondra Votava ondra.votava@phpsnippets.cz
 * @copyright Ondra Votava ondra.votava@phpsnippets.cz
 * @package   CreativeDesign\Utils
 *
 *          Text Helpers
 */
class Text
{
    /**
     * Truncate HTML text and restore tags
     *
     * @param string $string
     * @param int $limit
     * @param string $break
     * @param string $pad
     *
     * @return string
     */
    public static function truncateHtmlText($string, $limit = null, $break = null, $pad = null)
    {
        if ($limit === false) {
            return $string;
        } // false: disable truncate

        if (is_null($limit)) {
            $limit = 120;
        }   // use null to use global defalt limit
        if (is_null($pad)) {
            $pad = '…';
        }   // use null to use global defalt pad
        if (is_null($break)) {
            $break = ' ';
        }

        // pokud je text kratší než je požadováno vrátíme celý $string
        if (mb_strlen($string, 'UTF-8') <= $limit) {
            return $string;
        }
        // existuje $break mezi $limit a koncem $string?
        if (false !== ($breakpoint = mb_strpos($string, $break, $limit, "UTF-8"))) {
            if ($breakpoint < mb_strlen($string, 'UTF-8') - 1) {
                $string = mb_substr($string, 0, $breakpoint, "UTF-8") . $pad;
            }
        }

        return self::restoreHtmlTags($string, $pad);
        // return $string;
    }

    /**
     * @param string $string
     * @param string $pad
     *
     * @return string
     */
    public static function restoreHtmlTags($string, $pad = " ...")
    {
        //zkotrolujeme ze jsou vsechny tagy ukoncene (cele) pokud ne tak je odstranime
//        $prereg = "#((<[a-z1-9]+(?:\n| ).*)((?:>|$))|(<[a-z](?:>|$)))#miU";
        // https://kevin.deldycke.com/2007/03/ultimate-regular-expression-for-html-tag-parsing-with-php/
        $prereg = "/<\/?\w+((\s+(\w|\w[\w-]*\w)(\s*=\s*(?:\".*?\"|'.*?'|[^'\">\s]+))?)+\s*|\s*)\/?>/i";
        preg_match_all($prereg, $string, $match);
        $uncomplete = $match[0];
        $uncomplete = array_reverse($uncomplete);
        if (!self::endsWith($uncomplete[0], ">")) {
            $re = "#(" . $uncomplete[0] . ")$#miU";
            $string = preg_replace($re, $pad, $string, -1, $count);
        }
        // najdeme všechny otevřené tagy
        $re = "#<(?!meta|img|br|hr|input\b)\b([a-z1-9]+)((\n| ).*)?(?<![\/|\/ ])>#imU";
        preg_match_all($re, $string, $match);
        $openedtags = $match[1];
        // najdeme všechny uzavřené tagy
        preg_match_all("#<\/([a-z1-9]+)>#iU", $string, $match);
        $closedtags = $match[1];
        $len_opened = count($openedtags);
        // pokud jsou všechny tagy uzavřeny vrátime $string
        if (count($closedtags) == $len_opened) {
            return $string;
        }
        $openedtags = array_reverse($openedtags);
        // zavřeme tagy
        for ($i = 0; $i < $len_opened; $i++) {
            if (!in_array($openedtags[$i], $closedtags)) {
                $string .= "</" . $openedtags[$i] . ">";
            } else {
                unset ($closedtags[array_search($openedtags[$i], $closedtags)]);
            }
        }

        return $string;
    }

    /**
     * @param $haystack
     * @param $needle
     *
     * @return bool
     */
    public static function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }
}
