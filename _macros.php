<?php namespace ProcessWire;

wire($this->api_var)->_addMacro = array(

    'iff' => array(
        'iff',
        'if (null !== %node.word && %node.word) { $x = %node.word; ',
        '}'
    ),

    'page' => array(
        'page',
        '$p = \ProcessWire\wire("pages")->get(%node.word)',
        ';'
    ),

    'pages' => array(
        'pages',
        '$pArr = \ProcessWire\wire("pages")->find(%node.word)',
        ';'
    ),

    'setvar' => array(
        'setvar',
        '$vars = %node.array; ${$vars[0]}=$vars[1]',
        ';'
    )
);
