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

        if ($pageNum = wire('input')->pageNum) {
            if ($pageNum > 1) $class[] = "pagenum-" . $pageNum;
        }

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
 * LazySizes helper filter
 * Requires lazysizes.js added manually and adding the "lazyload" class to img
 * https://github.com/aFarkas/lazysizes
 *
 * <img src="{$page->images->first()->width(900)|lazy|noescape}" alt="" class="lazyload" />
 * <img src="{$page->images->first()->width(900)|lazy:3|noescape}" alt="" class="lazyload" />
 *
 */
$view->addFilter('lazy', function ($img = null, $divisor = null, $type = 'img') {

    $divisor = is_null($divisor) ? 4 : (int)$divisor;

    if (is_null($img) || !($img instanceof Pageimage) || $divisor <= 1) return false;

    $markup = '';

    // get width and height pixel values from the current resized image and resize the original
    $imgSmall = $img->getOriginal()->size($img->width / $divisor, $img->height / $divisor, array('quality' => 70));

    if ($type === 'img') {
        $markup = $imgSmall->url . '"data-src="' . $img->url;
    } else if ($type === 'bg') {
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
    if (!isset($args[1])) $args[1] = null;   // ensure there's a second parameter
    $args[] = 'bg';
    return $view->invokeFilter('lazy', $args);
});


// count PageArray
$view->addFilter('count', function ($selector = null) {

    if (is_null($selector))
        return false;

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

    if (strlen($url) == 0) return false;

    $defaults = array(
        'width' => 560,
        'height' => 315,
        'upscale' => true,
        'attr' => '',
        'wrapAttr' => 'class="embed-wrap"',
        'srcAttr' => 'src'
    );

    $args = is_array($args) ? array_merge($defaults, $args) : $defaults;

    extract($args);

    $width = !is_integer($width) ? 560 : $width;
    $height = !is_integer($height) ? 315 : $height;

    if ($upscale === false) $wrapAttr .= ' style="max-width:' . $width . 'px;" ';

    $ratio = round($height / $width * 100, 2);

    return <<< HTML
    <div $wrapAttr><div style="padding-bottom:$ratio%"><iframe width="$width" height="$height" $srcAttr="$url" $attr></iframe></div></div>
HTML;
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

    } else if ($encode == 'drupal') {

        $address = str_replace('@', '[at]', $address);
        $_text = $text == null ? $address : $_text;

        return '<a href="mailto:' . $address . '"' . $_extra . '>' . $_text . '</a>';

    } else if ($encode == 'texy') {
        $address = str_replace('@', '<!-- ANTISPAM -->&#64;<!-- /ANTISPAM -->', $address);
        $_text = $text == null ? $address : $_text;

        return '<a href="mailto:' . $address . '"' . $_extra . '>' . $_text . '</a>';
    }

    // no encoding
    return '<a href="mailto:' . $address . '"' . $_extra . '>' . $_text . '</a>';
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



// truncate html
$view->addFilter('truncatehtml', function () {
    return call_user_func_array(array('ProcessWire\Text', 'truncateHtmlText'), func_get_args());
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
 * Class Text
 * // http://www.phpsnippets.cz/latte-makro-pro-oriznuti-textu-s-html-tagy
 * @author Ondra Votava ondra.votava@phpsnippets.cz
 * @copyright Ondra Votava ondra.votava@phpsnippets.cz
 * @package CreativeDesign\Utils
 *
 *          Text Helpers
 */
class Text {
    /**
     * Truncate HTML text and restore tags
     * @param string $string
     * @param int $limit
     * @param string $break
     * @param string $pad
     *
     * @return string
     */
    public static function truncateHtmlText($string, $limit = null, $break = null, $pad = null)
    {
        if($limit === false) return $string; // false: disable truncate

        if(is_null($limit)) $limit = 120;   // use null to use global defalt limit
        if(is_null($pad)) $pad = '…';   // use null to use global defalt pad
        if(is_null($break)) $break = ' ';

        // pokud je text kratší než je požadováno vrátíme celý $string
        if (mb_strlen($string, 'UTF-8') <= $limit) return $string;
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
        bd($string);
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
