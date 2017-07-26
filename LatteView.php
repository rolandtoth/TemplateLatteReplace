<?php namespace ProcessWire;

use Latte\Macros\MacroSet;

/**
 * Class LatteView
 *
 * @package ProcessWire
 */
class LatteView
{

    public $tmp;


    function __construct()
    {
        $this->temp = function () {
            return $this->tmp;
        };
    }


    public function invokeFilter($name, $args)
    {
        if (!is_array($args)) {
            $args = array($args);
        }

        return $this->latte->invokeFilter($name, $args);
    }


    public function addMacro()
    {
        $args = func_get_args();

        $set = new MacroSet($this->latte->getCompiler());

        if (!is_array($args)) {
            $args = array($args);
        }

        call_user_func_array(array($set, 'addMacro'), $args);
    }


    public function addFilter($name, $closure)
    {
        $this->latte->addFilter($name, $closure);
    }


    public function savetemp($data)
    {
        return $this->tmp = $data;
    }
}