<?php

    /**
     * Global helper function for string translation.
     *
     * @param array|string $args (text, context, textdomain)
     *
     * @return string
     */
    function _t($args = null) {

        if (!is_array($args)) $args = func_get_args();

        $text = isset($args[0]) ? $args[0] : "";

        if ($text == "") return "";

        $textdomain = isset($args[2]) ? $args[2] : "/site/templates/_strings.php";

        $string = isset($args[1]) ? ProcessWire\_x($text, $args[1], $textdomain) : ProcessWire\__($text, $textdomain);

        return html_entity_decode($string);
    }

    /**
     * Global helper function for pluralization translation.
     *
     * @param array|string $args
     *
     * @return string
     */
    function _p($args = null) {

        if (!is_array($args)) {
            $args = func_get_args();
        }

        $singular = isset($args[0]) ? $args[0] : "";
        $plural = isset($args[1]) ? $args[1] : "";
        $count = isset($args[2]) ? $args[2] : 1;
        $replacements = isset($args[3]) ? $args[3] : array();

        // if 4 parameters are passed then add the fourth as the replacement array
        if (is_array($replacements) && count($replacements)) {
            return vsprintf(_t(ProcessWire\_n($singular, $plural, $count)), $replacements);
        } else {
            return _t(ProcessWire\_n($singular, $plural, $count));
        }

    }

    // alias for _p()
    function __p() {
        return call_user_func_array('_p', func_get_args());
    }