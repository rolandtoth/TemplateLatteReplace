<?php namespace Nette\Application\UI;

/**
 * fake classes to set default Latte template
 */

if (!class_exists('Presenter')) {
    class Presenter {
    }
}

class FakePresenter extends Presenter {

    public $viewsDir = 'views';
    public $defaultLayoutFile = '@layout.latte';

    public function findLayoutTemplateFile() {
        return \ProcessWire\wire('config')->paths->templates . trim($this->viewsDir, '/\\') . '/' . trim($this->defaultLayoutFile, '/\\');
    }

}

