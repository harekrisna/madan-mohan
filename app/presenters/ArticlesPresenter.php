<?php

namespace App\Presenters;

use Nette,
	App\Model;

class ArticlesPresenter extends BasePresenter {

	public function renderDefault() {
    
	}
	
	public function renderArticle($id) {
	$this->setView($id);
    $this->template->id = $id;
	}
}
