<?php

namespace App\AdminModule\Presenters;
use Nette,
	App\Model;
use Nette\Diagnostics\Debugger;
use Nette\Application\UI\Form;

final class AddressPresenter extends BasePresenter {    
    protected function startup()  {
        parent::startup();
    
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
    }  

    public function beforeRender() {
        $this->template->menu = array();
        $this->setLayout("wide.layout");
    }
        
    function actionList() {
   	    $order_data = $this->orderData
                           ->findAll();
		
        $cartages = $this->cartage
 		                 ->findAll();
 
        $this->template->addresses = $order_data;
        $this->template->cartages = $cartages;
    }

    public function handleSetCartage($data_id, $cartage_id) {
        $this->orderData
             ->update($data_id, array("cartage_id" => $cartage_id));
             
        $this->terminate();
    } 
    
}
