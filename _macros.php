<?php namespace ProcessWire;

use Latte\MacroNode;
use Latte\PhpWriter;

$view->addMacro(
    'iff',
    'if (!empty(%node.word) && %node.word) { $x = %node.word;',
    '}'
);

$view->addMacro(
    'ifloggedin',
    'if (\ProcessWire\wire("user")->isLoggedIn()) {',
    '}'
);

$view->addMacro(
    'page',
    '$p = \ProcessWire\wire("pages")->get(%node.word)',
    ';'
);

$view->addMacro(
    'ifpage',
    'if (%node.word instanceof \ProcessWire\Page && %node.word->id) { $p = %node.word;',
    '}'
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


$view->addMacro(
    'editlink',
    function (MacroNode $node, PhpWriter $writer) {
        return $writer->write('
            $args = %node.array;
            $wire = \ProcessWire\wire();
            $user = $wire->user;
            
            $defaults = array(
                "target" => $wire->page,
                "text" => "Edit",
                "attrs" => "",
                "urlparams" => ""
            );
            
            // merge common defaults from $view->editlinkDefaults (eg. ready.php)
            if(!empty($editlinkDefaults)) {
                $defaults = array_merge($defaults, $editlinkDefaults);
            }
            
            // merge parameters from latte file
            $args = array_merge($defaults, $args);
            
            if(count($args) == 1) $args = array("target" => $args[0]);
            
            // if first argument is instance of Page, set target page
            if(isset($args[0]) && $args[0] instanceof \processWire\Page) {
                $args["target"] = $args[0];
                unset($args[0]);
            }

            extract($args);
            
            // append $args to default args set in $view->editlinkDefaults if $attrs starts with "+"
            if (isset($attrs) && substr($attrs, 0, 1) === "+") {
                $attrs = $defaults["attrs"] . " " . ltrim($attrs, "+");
            }
            
            if (is_numeric($target)) $target = $wire->pages->get($target);
            
            if ($target instanceof \ProcessWire\Page && $target->editable() && $target->template != "admin" && $user->isLoggedin()) {
	            
	            $edit_url = $target->editUrl;
	            $urlparams = ($user->language ? "&language=" . $user->language->id : "") . $urlparams;
	            
	            echo <<< HTML
    <a href="{$edit_url}{$urlparams}" data-editlink $attrs>$text</a>
HTML;
	            
            }
        ');
    }
);


$view->addMacro(
    'minify',
    function (MacroNode $node, PhpWriter $writer) {
        return $writer->write('
        
            ob_start(function ($s, $phase) {
            
                // 0: replace newlines
                //$html = preg_replace("/\r\n|\r|\n/", "", $s);
            
                // 1: remove whitespace from between tags that are not on the same line.
                $html=preg_replace(\'~>\s*\n\s*<~\', \'><\', $s); 
                
                // 2: replace all repeated whitespace with a single space.
                static $strip = true; 
                $html=LR\Filters::spacelessHtml($html, $phase, $strip); 
                
                return $html;
                
            }, 4096);'
        );
    },
    'ob_end_flush();'
);


// unpublished, hidden, locked, system, trash, draft + public, published
$view->addMacro(
    'ifstatus',
    function (MacroNode $node, PhpWriter $writer) {
        return $writer->write('
            $args = %node.array;
            $_p = isset($args[0]) ? $args[0] : false;
            $_status = isset($args[1]) ? $args[1] : false;
            $reversed = false;
            
            if(is_numeric($_p)) {
                $_p = \ProcessWire\wire("pages")->get($_p);
            }
            
            if($_p && $_status) {
            
                $_cond = true;
            
                if(!is_array($_status)) {
                    $_status = array($_status);
                }
                
                foreach($_status as $_s) {
                
                    $_s = trim($_s);
            
                    if(strpos($_s, "!") === 0) {
                        $reversed = true;
                        $_s = ltrim($_s, "!");
                    }
                
                    if($_s === "published") {   // not in API
                        $cond = !$_p->hasStatus("unpublished") && !$_p->hasStatus("hidden") && !$_p->isTrash();
                    } elseif($_s === "public") {
                        $cond = $_p->isPublic();    // not in API
                    } else {
                        $cond = $_p->hasStatus($_s);
                    }
                    
                    if($reversed) {
                        $cond = !$cond;
                    }
                    
                    $_cond = $_cond && $cond;
                }
                
                if($_cond) {
        ');
    },
    '}}'
);
