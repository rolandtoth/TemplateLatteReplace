<?php namespace ProcessWire;

use Latte\MacroNode;
use Latte\PhpWriter;

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


$view->addMacro(
    'editlink',
    function (MacroNode $node, PhpWriter $writer) {
        return $writer->write('
            $args = %node.array;
            $wire = \ProcessWire\wire();
            $user = $wire->user;
            
            $defaults = array(
                "target" => $wire->page,
                "class" => "edit-link",
                "text" => "Edit",
                "targetAttr" => "_blank"
            );
            
            if(count($args) == 1) $args = array("target" => $args[0]);
            
            $args = array_merge($defaults, $args);

            extract($args);
        
            if (is_numeric($target)) $target = $wire->pages->get($target);
            
            if ($target instanceof \ProcessWire\Page && $target->editable() && $target->template != "admin" && $user->isLoggedin()) {
	            
	            $lang = $user->language ? "&language=" . $user->language->id : "";
	            $edit_url = $target->editUrl . $lang;
	            
	            echo <<< HTML
    <a href="$edit_url" class="$class" target="$targetAttr">$text</a>
HTML;
	            
            }
        ');
    }
);


$view->addMacro(
    'minify',
    function (MacroNode $node, PhpWriter $writer) {
        return $writer->write('
        
            $enable_experimental = %node.word === true;
                
            ob_start(function ($s, $phase) use ($enable_experimental) {
            
                // 1: remove whitespace from between tags that are not on the same line.
                
                $html=preg_replace(\'~>\s*\n\s*<~\', \'><\', $s); 
                
                // 2: replace all repeated whitespace with a single space.
                
                static $strip = true; 
                $html=LR\Filters::spacelessHtml($html, $phase, $strip); 
                
                // 3: experimental operations
                
                if($enable_experimental) {
                
                    // remove quotes from attributes only if there is no space inside
                    $html=preg_replace("/(?s)<pre[^<]*>.*?<\/pre>(*SKIP)(*F)|(?s)<code[^<]*>.*?<\/code>(*SKIP)(*F)|\s(class|id|alt|type|http-equiv|target|method|placeholder|value|title|hreflang|lang|dir|charset|content|name|for|rel)=(\"|\')(\S+?)(\"|\')/ims", " $1=$3", $html); 
                   
                   // "; </script"
                    $html=preg_replace("/(;\s\<\/script\>)/", "\<\/script\>", $html);
                     
                     // " />"
                    $html=preg_replace("/(\s+\/>)/", "\/\>", $html); 
                    
                    // " >"
                    $html=preg_replace("/(\s\>)/", "\>", $html); 
                    
                    // data-* only if value contains only alphanumeric
                    $html=preg_replace("/\s(data-[a-zA-Z-]+)=(\"|\')([a-zA-Z0-9_-]+?)(\"|\')/ims", " $1=$3", $html);
                    
        
                    // remove quotes from attributes having numbers only (disallow dot)
                    $html=preg_replace("/\s(x|y|width|height|size|tabindex|cols|rows|maxlength)=(\"|\')([0-9]+)(\"|\')/ims", " $1=$3", $html);
                    
                    // add space between attribute value and closing tag (for numbers)
                    $html=preg_replace("/\s(x|y|width|height)=([0-9]+)\/\>/ims", " $1=$2 ", $html);
                    
                    // remove type=text
                    $html=preg_replace("/\s(type=text)/ims", "", $html);
                }
                
                return $html;
                
            }, 4096);'
        );
    },
    'ob_end_flush();'
);

//$view->addMacro(
//    'minify',
//    'ob_start(function ($s, $phase) {
//
//            static $strip = TRUE;
//
//            // 1: remove whitespace from between tags that are not on the same line.
//            $html=preg_replace(\'~>\s*\n\s*<~\', \'><\', $s);
//
//            // 2: replace all repeated whitespace with a single space.
//            $html=LR\Filters::spacelessHtml($html, $phase, $strip);
//
//            // remove quotes from attributes only if there is no space inside
//            $html=preg_replace("/(?s)<pre[^<]*>.*?<\/pre>(*SKIP)(*F)|(?s)<code[^<]*>.*?<\/code>(*SKIP)(*F)|\s(class|id|alt|type|http-equiv|target|method|placeholder|value|title|hreflang|lang|dir|charset|content|name|for|rel)=(\"|\')(\S+?)(\"|\')/ims", " $1=$3", $html);
//
//           // "; </script"
//            $html=preg_replace("/(;\s\<\/script\>)/", "\<\/script\>", $html);
//
//             // " />"
//            $html=preg_replace("/(\s+\/>)/", "\/\>", $html);
//
//            // " >"
//            $html=preg_replace("/(\s\>)/", "\>", $html);
//
//            // data-* only if value contains only alphanumeric
//            $html=preg_replace("/\s(data-[a-zA-Z-]+)=(\"|\')([a-zA-Z0-9_-]+?)(\"|\')/ims", " $1=$3", $html);
//
//
//            // remove quotes from attributes having numbers only (disallow dot)
//            $html=preg_replace("/\s(x|y|width|height|size|tabindex|cols|rows|maxlength)=(\"|\')([0-9]+)(\"|\')/ims", " $1=$3", $html);
//
//            // add space between attribute value and closing tag (for numbers)
//            $html=preg_replace("/\s(x|y|width|height)=([0-9]+)\/\>/ims", " $1=$2 ", $html);
//
//            // remove type=text
//            $html=preg_replace("/\s(type=text)/ims", "", $html);
//
//            return $html;
//        }
//     , 4096);',
//    'ob_end_flush();'
//);
