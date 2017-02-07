<?php namespace ProcessWire;


$view->addMacro(
    'iff',
    'if (!empty(%node.word) && %node.word) { $x = %node.word;',
    '}'
);

$view->addMacro(
    'page',
    '$p = \ProcessWire\wire("pages")->get(%node.word)',
    ';'
);

$view->addMacro(
    'pages',
    '$pArr = \ProcessWire\wire("pages")->find(%node.word)',
    ';'
);

$view->addMacro(
    'setvar',
    '$vars = %node.array; ${$vars[0]}=$vars[1]',
    ';'
);
