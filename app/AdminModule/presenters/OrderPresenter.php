<?php

namespace App\AdminModule\Presenters;
use Nette,
	App\Model;
use Nette\Diagnostics\Debugger;
use Nette\Application\UI\Form;

final class OrderPresenter extends BasePresenter {    
    /** @persistent int*/
    public $month_offset;

    protected function startup()  {
        parent::startup();
    
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
    }  
    
    function beforeRender() {
       $actual_year = date("Y", strtotime("first day of this month ".($this->month_offset)." month"));
       $previous_year = date("Y", strtotime("first day of this month ".($this->month_offset-1)." month"));
       $next_year = date("Y", strtotime("first day of this month ".($this->month_offset+1)." month"));       

       $actual_month = date("n", strtotime("first day of this month ".($this->month_offset)." month"));
       $previous_month = date("n", strtotime("first day of this month ".($this->month_offset-1)." month"));
       $next_month = date("n", strtotime("first day of this month ".($this->month_offset+1)." month"));
              
       $next_title = $next_month.".".$next_year;
       $previous_title = $previous_month.".".$previous_year;

       $dates = $this->date
                     ->dates_month($actual_month, $actual_year);

       $this->template->dates = $dates;
       $this->template->offset = $this->month_offset;       
       $this->template->next = $next_title;
       $this->template->previous = $previous_title;       
    }
    
    function actionDefault() {
        $this->month_offset = 0;
        $this->redirect('list');
    }
    
    function actionSetOffset($offset) {
        $this->month_offset = $offset;
        if($offset == 0)
            $this->redirect('list');
        
        $year = date("Y", strtotime("first day of this month ".$offset." month"));
        $month = date("m", strtotime("first day of this month ".$offset." month"));
           
        $this->redirect('list', $year."-".$month."-01");
    }
	
	function actionList($date) {
		if(!$this->isAjax()) {
	        if(!$date)
    	        $date = date("Y-m-d");
				
			$this['insertOrderForm']['lunch_date']->setValue($date);
		}
	}

    function renderList($date) {
        if(!$date)
            $date = date("Y-m-d");
            
        $year = substr($date, 0, 4);
        $month = substr($date, 5, 2);

        $dates = $this->date
                      ->dates_month($month, $year);
        
        $cartages = $this->cartage
                         ->findAll();
        
        $orders = $this->order
                       ->findAll()
                       ->where("lunch.lunch_date = ?", $date);

        $this->template->dates = $dates;
        $this->template->date = $date;
        $this->template->orders = $orders;
        $this->template->cartages = $cartages;
         Debugger::fireLog("render");
    }  
    
    public function handleSetCartage($order_id, $cartage_id) {
        $this->order
             ->update($order_id, array("cartage_id" => $cartage_id));
        
        $this->payload->success = TRUE;
		$this->sendPayload();
        $this->terminate();
    } 
    
    protected function createComponentInsertOrderForm(){
	   	$form = new Nette\Application\UI\Form();

	    $cartages = $this->cartage
                         ->findAll();
		
		$pairs = $cartages->fetchPairs('id', 'abbreviation');
								 
	    $form->addHidden('lunch_date', 'Date:');
        
	    $form->addText('person_name', 'Jméno:', 40, 255)
      	     ->setRequired('Zadejte jméno.');
      	     
	    $form->addText('address', 'Adresa:', 50, 255)
      	     ->setRequired('Zadejte adresu.');
      	     
	    $form->addText('phone', 'Telefon:', 9, 9);                   
   	    $form->addSelect('cartage_id', 'Rozvoz:', $pairs)
	    	 ->setPrompt("");;
   		$form->addText('lunch_count', 'Počet objedů:', 3, 2)
      	     ->setRequired('Zadejte počet obědů.');
   		
   		$form->addSubmit('insert', 'Vložit');
   		
        $form->onSuccess[] = array($this, 'insertOrder');
        return $form;
    }
    
    public function insertOrder(Nette\Application\UI\Form $form)    {
        $values = $form->getValues();
        
        $lunch = $this->lunch
                      ->findBy(array("lunch_date" => $values['lunch_date']))
                      ->fetch();

        $order_data = $this->orderData
        				   ->findBy(array("person_name" => $values['person_name'],
        				   				  "address" => $values['address'],
        				   				  "phone" => $values['phone']))
        				   ->fetch();
        
        if($order_data) {
        	$data_id = $order_data->id;
        }
        else {
	        $data_id = $this->orderData
     	   		 	  	    ->insert($values['person_name'], $values['address'], $values['phone'], "", $values['cartage_id']);
        }
        
        $this->order
             ->insert($data_id, 
                      $lunch->id,
                      $values['cartage_id'],
                      $values['lunch_count']);  
        
        $this->redirect('list', array("date" => $values['lunch_date']));
    }

	public function actionAddOrders() {
		$empty_days = 0;
        $now = time();
		$time_deadline = "12:00:00";
    	
	    foreach($this->lunch->getWeekDates() as $date) {
	        $lunch = $this->lunch
	                      ->findBy(array("lunch_date" => $date))
			 			  ->fetch();
			
			$day_name = $this->lunch
							 ->getAbbrFromDate($date);
			
			$day_timestamp = $this->lunch->strtotime($date." ".$time_deadline);
							 
			if(!$lunch || $lunch['nocook'] == 1 || $day_timestamp - $now < 0) {
				$empty_days++;
				$this['insertOrderExtendedForm']['this_week'][$day_name]->setDisabled();
			}
	    }
	    
	    if($empty_days == 5) {
		    $this['insertOrderExtendedForm']['this_week_all']->setDisabled();
	    }
	    	    

		$empty_days = 0;
    	
	    foreach($this->lunch->getWeekDates(1) as $date) {
	        $lunch = $this->lunch
	                      ->findBy(array("lunch_date" => $date))
			 			  ->fetch();			

			$day_name = $this->lunch
							 ->getAbbrFromDate($date);

			if(!$lunch || $lunch['nocook'] == 1) {
				$empty_days++;
				$this['insertOrderExtendedForm']['next_week'][$day_name]->setDisabled();
			}
	    }
	    
	    if($empty_days == 5) {
		    $this['insertOrderExtendedForm']['next_week_all']->setDisabled();
	    }
	}

	protected function createComponentInsertOrderExtendedForm(){
	   	$form = new Nette\Application\UI\Form();

	    $cartages = $this->cartage
                         ->findAll();
		
		$pairs = $cartages->fetchPairs('id', 'abbreviation');
        
	    $form->addText('person_name', 'Jméno:', 40, 255)
      	     ->setRequired('Zadejte jméno.');
      	     
	    $form->addText('address', 'Adresa:', 40, 255)
      	     ->setRequired('Zadejte adresu.');
      	     
	    $form->addText('phone', 'Telefon:', 9, 9);                   

   	    $form->addSelect('cartage_id', 'Rozvoz:', $pairs)
	    	 ->setPrompt("");

	    $this_week = $form->addContainer('this_week');

        $form->addText("this_week_all", "")
	        		  ->setType("number")
     	        	  ->setAttribute('min', '0')
     	        	  ->setAttribute('max', '99')
     	        	  ->setAttribute('autocomplete', 'off')
       	        	  ->setDefaultValue('0')
    		  		  ->addCondition($form::FILLED)
					  	  ->addRule(Form::RANGE, 'Počet musí být v rozsahu %d to %d', array(0, 99));

	    foreach($this->lunch->getWeekDates() as $date) {
  	    	$day_name = strtolower(date("l",strtotime($date)));
  	    	
	        $this_week->addText($day_name, date("d.m.Y", strtotime($date)))
	        		  ->setType("number")
     	        	  ->setAttribute('min', '0')
     	        	  ->setAttribute('max', '99')
     	        	  ->setAttribute('autocomplete', 'off')
       	        	  ->setDefaultValue('0')
    		  		  ->addCondition($form::FILLED)
					  	  ->addRule(Form::RANGE, 'Počet musí být v rozsahu %d to %d', array(0, 99));			
	    }


	    $next_week = $form->addContainer('next_week');    	      	  
	    
        $form->addText("next_week_all", "")
	         ->setType("number")
     	     ->setAttribute('min', '0')
     	     ->setAttribute('max', '99')
     	     ->setAttribute('autocomplete', 'off')
       	     ->setDefaultValue('0')
    		 ->addCondition($form::FILLED)
				 ->addRule(Form::RANGE, 'Počet musí být v rozsahu %d to %d', array(0, 99));        	
				 
	    foreach($this->lunch->getWeekDates(1) as $date) {
	    	$day_name = strtolower(date("l",strtotime($date)));
	        
	        $next_week->addText($day_name, date("d.m.Y", strtotime($date)))
	           		  ->setType("number")
					  ->setAttribute('min', '0')
					  ->setAttribute('max', '99')
					  ->setAttribute('autocomplete', 'off')
					  ->setDefaultValue('0')
					  ->addCondition($form::FILLED)
					  	  ->addRule(Form::RANGE, 'Počet musí být v rozsahu %d to %d', array(0, 99));
	    }
	    	    
   		$form->addSubmit('insert', 'Přidat objednávky');
   		
        $form->onSuccess[] = array($this, 'insertOrderExtendedSubmit');
        return $form;
    }


    public function insertOrderExtendedSubmit(Nette\Application\UI\Form $form)    {
        $values = $form->getValues();

        $order_data = $this->orderData
        				   ->findBy(array("person_name" => $values['person_name'],
        				   				  "address" => $values['address'],
        				   				  "phone" => $values['phone']))
        				   ->fetch();

        if($order_data) {
        	$data_id = $order_data->id;
        }
        else {
	        $data_id = $this->orderData
     	   		 	  	    ->insert($values['person_name'], $values['address'], $values['phone'], "", $values['cartage_id']);
        }
		
		foreach($values->this_week as $day_name => $lunch_count) {
			if($lunch_count > 0) {
				$lunch_date = $this->lunch->getWeekDayDate($day_name);
				
		        $lunch = $this->lunch
		                      ->findBy(array("lunch_date" => $lunch_date))
				 			  ->fetch();

				$this->order
					 ->insert($data_id, $lunch->id, $values['cartage_id'], $lunch_count);  				
			}
		}
		
		foreach($values->next_week as $day_name => $lunch_count) {
			if($lunch_count > 0) {
				$lunch_date = $this->lunch->getWeekDayDate($day_name, 1);
				
		        $lunch = $this->lunch
		                      ->findBy(array("lunch_date" => $lunch_date))
				 			  ->fetch();

				$this->order
					 ->insert($data_id, $lunch->id, $values['cartage_id'], $lunch_count);  				
			}
		}
		
        $this->flashMessage('Objednávky byly přidány', 'success');
        $this->redirect('addOrders');

	}

    
    public function handleEditField($order_id, $column, $value) {		
		if($column == "lunch_count") {
			$this->order
				 ->update($order_id, array($column => $value));
			
			$updateData = $this->order
	 				 		   ->find($order_id);
	 		
	 		$order = $this->order
 				 		  ->findBy(array("lunch_id" => $updateData->lunch_id));
			
			$this->payload->lunch_sum = $order->sum("lunch_count");
		}
		else {
			$order = $this->order
				 		  ->find($order_id);
			
			$this->orderData
				 ->update($order->order_data_id, array($column => $value));
				 
			$updateData = $this->orderData
			   			  	   ->find($order->order_data_id);
		
		}
		
		$this->payload->success = TRUE;
		$this->payload->value = $updateData->$column;
		$this->sendPayload();
        $this->terminate();	    
    }
    
    public function handleDeleteOrder($order_id) {
		$order = $this->order
	 		 	   	  ->find($order_id);
	 		 	   	  
    	$this->order
             ->delete($order_id);
	 		
 		$order = $this->order
			 		  ->findBy(array("lunch_id" => $order->lunch_id));
			
		$this->payload->lunch_sum = $order->sum("lunch_count");
		$this->payload->success = TRUE;
		$this->sendPayload();
        $this->terminate();	                
    }
        
    public function actionPrintout($date) {
        $this->setLayout("layout.printout");
    }
        
	public function renderPrintout($date) {            
    	$cartages = array();
    	
        $cartages_db = $this->cartage
                         	->findAll();
        
        foreach ($cartages_db as $cartage) {
	        $orders = $this->order
    	                   ->findAll()
						   ->where("lunch.lunch_date = ?", $date)
						   ->where("order.cartage_id = ?", $cartage->id)
						   ->order("order_data.address");
        	
	    	$cartages[$cartage->id] = $orders;
        }

		$orders = $this->order
	                   ->findAll()
	                   ->where("lunch.lunch_date = ?", $date)
					   ->where("cartage_id IS NULL");
    	
    	$cartages[] = $orders;

        $this->template->cartages = $cartages;
    }

}
