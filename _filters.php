<?php namespace ProcessWire;

wire($this->api_var)->_filters['activeClass'] = function ($currentPage, $className = 'active') {
    $page = wire('page');

    return ($page == $currentPage || $page->parentsUntil(1)->has($currentPage)) ? $className : '';
};


wire($this->api_var)->_filters['bodyClass'] = function ($p) {

    $id    = $p->id;
    $class = "";

    if (!empty($id)) {

        $class = array();

        $class[] = ($id == 1) ? "home" : "page-" . $id;

        if (!in_array($p->parent->id, array(0, 1))) {
            $class[] = "parent-" . $p->parent->id;
        }

        if (wire('user')->language) {
            $class[] = "lang-" . wire('user')->language->name;
        }

        $class[] = "template-" . $p->template->name;

        $class = implode(" ", $class);
    }

    return $class;
};


wire($this->api_var)->_filters['getPage'] = function ($selector = null) {

    if (is_null($selector)) {
        return false;
    }

    return wire('pages')->get($selector);
};


wire($this->api_var)->_filters['getPages'] = function ($selector = null, $extraSelector = null) {

    if (is_null($selector)) {
        return false;
    }

    if (!is_null($extraSelector)) {
        $selector .= ',' . $extraSelector;
    }

    return wire('pages')->find($selector);
};

// remove everything but numbers
wire($this->api_var)->_filters['onlyNumbers'] = function ($str) {
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
wire($this->api_var)->_filters['niceUrl'] = function ($url = null, $remove = 'httpwww/') {

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
