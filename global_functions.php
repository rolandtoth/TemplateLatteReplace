<?php

    /**
     * Global helper function for string translation.
     *
     * @param array|string $args
     *
     * @return string
     */
    function t($args = null) {

        $context = "General";
        $textdomain = "/site/templates/_strings.php";

        if (!is_array($args)) {
            $args = func_get_args();
        }

        $text = isset($args[0]) ? $args[0] : "";
        $context = isset($args[1]) ? $args[1] : $context;
        $textdomain = isset($args[2]) ? $args[2] : $textdomain;

        return ProcessWire\_x($text, $context, $textdomain);
    }

    /**
     * Global helper function for pluralization translation.
     *
     * @param array|string $args
     *
     * @return string
     */
    function n($args = null) {

        if (!is_array($args)) {
            $args = func_get_args();
        }

        $singular = isset($args[0]) ? $args[0] : "";
        $plural = isset($args[1]) ? $args[1] : "";
        $count = isset($args[2]) ? $args[2] : 1;

        return sprintf(t(ProcessWire\_n($singular, $plural, $count)), $count);
    }
