<?php

namespace App\Presenters;

use Nette,
	App\Model;

class ArticlesPresenter extends BasePresenter {

	public function renderDefault() {
    
	}
	
	public function renderArticle($template) {
		$this->setView($template);
	}
}
