<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    protected $date;    
    protected $preparation;
    protected $category;
    protected $allergen;
    protected $preparationAllergen;
    protected $menu;
    protected $lunch;
    protected $lunchPreparation;
    protected $order;
    protected $orderData;    
    protected $cartage;    

    protected function startup()	{
		parent::startup();
        $this->date = $this->context->date;
        $this->preparation = $this->context->preparations;
        $this->category = $this->context->category;
        $this->allergen = $this->context->getService("allergen");
        $this->preparationAllergen = $this->context->getService("preparationAllergen");
        $this->menu = $this->context->menu;
        $this->lunch = $this->context->lunch;
        $this->lunchPreparation = $this->context->lunchPreparation; 
        $this->order = $this->context->order;
        $this->orderData = $this->context->orderData;
        $this->cartage = $this->context->cartage;
    }
   	
    public function handleSignOut() {
        $this->getUser()->logout();
    $this->redirect('Sign:in');
  }
}

