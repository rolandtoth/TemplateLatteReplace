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

        if (!in_array($p->parent->id, array(0, 1))) {
            $class[] = "parent-" . $p->parent->id;
        }

        if ($this->wire('user')->language) {
            $class[] = "lang-" . $this->wire('user')->language->name;
        }

        $class[] = "template-" . $p->template->name;

        $class = implode(" ", $class);
    }

    return $class;
};


$this->wire($this->api_var)->_filters['getPage'] = function ($selector = null) {

    if (is_null($selector)) {
        return false;
    }

    return $this->wire('pages')->get($selector);
};


$this->wire($this->api_var)->_filters['getPages'] = function ($selector = null, $extraSelector = null) {

    if (is_null($selector)) {
        return false;
    }

    if (!is_null($extraSelector)) {
        $selector .= ',' . $extraSelector;
    }

    return $this->wire('pages')->find($selector);
};


// get page field
// use getParent if PageArray is passed
$this->wire($this->api_var)->_filters['get'] = function ($selector = null, $field = 'title') {

    if (is_null($selector)) {
        return false;
    }

    // needed for $pageArray|getParent|get to work
    if ($selector instanceOf Page) {
        $selector = $selector->id;
    }

    return $this->wire('pages')->get($selector)->$field;
};


// get parent
$this->wire($this->api_var)->_filters['getParent'] = function ($selector = null) {

    if (is_null($selector)) {
        return false;
    }

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
};
