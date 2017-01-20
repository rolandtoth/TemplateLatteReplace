<?php namespace ProcessWire;

$this->wire($this->api_var)->_filters['activeClass'] = function ($currentPage, $className = 'active') {
    $page = $this->wire('page');

    return ($page == $currentPage || $page->parentsUntil(1)->has($currentPage)) ? $className : '';
};


$this->wire($this->api_var)->_filters['bodyClass'] = function ($p) {

    $id    = $p->id;
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
};


$this->wire($this->api_var)->_filters['getPage'] = function ($selector = null) {

    if (is_null($selector))
        return false;


    return $this->wire('pages')->get($selector);
};


$this->wire($this->api_var)->_filters['getPages'] = function ($selector = null, $extraSelector = null) {

    if (is_null($selector))
        return false;


    if (!is_null($extraSelector))
        $selector .= ',' . $extraSelector;


    return $this->wire('pages')->find($selector);
};


$this->wire($this->api_var)->_filters['breadcrumb'] = function ($p = null, $args = null) {

    if (is_null($p))
        return false;

    if (!is_array($args))
        $args = array($args);

    $markup = '';

    $root           = isset($args['root']) ? $args['root'] : 1;
    $addHome        = isset($args['addHome']) ? $args['addHome'] : true;
    $addCurrent     = isset($args['addCurrent']) ? $args['addCurrent'] : false;
    $addCurrentLink = isset($args['addCurrentLink']) ? $args['addCurrentLink'] : false;
    $class          = isset($args['class']) ? $args['class'] : 'breadcrumb';
    $id             = isset($args['id']) ? $args['id'] : '';
    $addAttributes  = isset($args['addAttributes']) ? true : false;

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
};


// get page field
// use getParent if PageArray is passed
$this->wire($this->api_var)->_filters['get'] = function ($selector = null, $field = 'title') {

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
};

// count PageArray
$this->wire($this->api_var)->_filters['count'] = function ($selector = null) {

    if (is_null($selector))
        return false;

    return $this->wire('pages')->find($selector)->count();
};


// get parent
$this->wire($this->api_var)->_filters['getParent'] = function ($selector = null) {

    if (is_null($selector))
        return false;

    return $this->wire('pages')->get($selector)->parent();
};


// remove everything but numbers
$this->wire($this->api_var)->_filters['onlyNumbers'] = function ($str) {
    return preg_replace("/[^0-9]/", "", $str);
};


/**
 * Remove http, www from links
 *
 * @param string $url
 *
 * @param string $remove
 *
 * @return mixed|string
 */
$this->wire($this->api_var)->_filters['niceUrl'] = function ($url = null, $remove = 'httpwww/') {

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
};
