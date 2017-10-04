<?php

namespace App\Presenters;
use Nette\Application\UI\Form;
use Nette,
	App\Model;
use Nette\Diagnostics\Debugger;
use Nette\Database\Context;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;
use DateTime;

class MenuPresenter extends BasePresenter {
	private $deadline_time = "9:15:00";
	
    private $days = array("monday" => "Po", 
                          "tuesday" => "Út",
                          "wednesday" => "St",
                          "thursday" => "Čt",
                          "friday" => "Pá");
    
	public function renderDefault() {	
        $this->template->week_title = $this->menu->getWeekTitle(0, $this->locale);
        $this->template->lunch = $this->lunch->getWeekLunchs();
        
        $lunchs = $this->lunch
                       ->findAll()
                       ->where("lunch_date", $this->lunch->getWeekDates(+1));

        if($lunchs->count() == 5) {
            $show_next_week = true;
            $this->template->next_week_title = $this->menu->getWeekTitle(+1, $this->locale);
            $this->template->lunch_next_week = $this->lunch->getWeekLunchs(+1);
        }
        else {
            $show_next_week = false;
            $this->template->next_week_title = "";
            $this->template->lunch_next_week = array();
        }
        
        $now = time();
        $show_second_slide_from = $this->lunch->strtotime("friday this week 16:00:00");
        $start_slide = $show_second_slide_from < $now ? 1 : 0;
        if($show_next_week === false)
            $start_slide = 0;        

        $this->template->show_next_week = $show_next_week;
        $this->template->start_slide = $start_slide;   
        $this->template->today = date('N');         
	}
	
	public function actionOrder() {
	    $this->template->ip = $_SERVER['REMOTE_ADDR'];
	    $lunchs = $this->lunch->getWeekLunchs();
        
        $now = time();
		$time_deadline = $this->deadline_time;
        
        
        foreach ($this->days as $day => $day_cz) {
            $this_week_day = $this->lunch
            					  ->strtotime("{$day} this week".$time_deadline);
                        
            if($this_week_day - $now < 0 || $lunchs[$day]['nocook'] == 1) {
                $this['orderForm']['this_week'][$day]->setDisabled();
                $lunchs[$day]['disabled'] = true;
            }    
            else
                $lunchs[$day]['disabled'] = false;
        }
        
        $this->template->week_title = $this->menu->getWeekTitle(0, $this->locale);
        $this->template->lunch = $lunchs;

        // DALŠÍ TÝDEN        
        $lunchs = $this->lunch
                       ->findAll()
                       ->where("lunch_date", $this->lunch->getWeekDates(+1));

        if($lunchs->count() == 5) {
            $lunchs_next_week = $this->lunch->getWeekLunchs(+1);
            
            foreach ($this->days as $day => $day_cz) {
                if($lunchs_next_week[$day]['nocook'] == 1)
                    $this['orderForm']['next_week'][$day]->setDisabled();
            }
            
            $show_next_week = true;
            $this->template->next_week_title = $this->menu->getWeekTitle(+1, $this->locale);
            $this->template->lunch_next_week = $lunchs_next_week;
        }
        else {
            $show_next_week = false;
            $this->template->next_week_title = "";
            $this->template->lunch_next_week = array();
        }
        
        $show_second_slide_from = $this->lunch->strtotime("friday this week 16:00:00");
        $start_slide = $show_second_slide_from < $now ? 1 : 0;
        if($show_next_week === false)
            $start_slide = 0;
        
        $this->template->show_next_week = $show_next_week;
        $this->template->start_slide = $start_slide;
        $this->template->today = date('N');

	}

    protected function createComponentOrderForm(){
	   	$form = new Nette\Application\UI\Form();
	    $form->setTranslator($this->translator);

	    $form->addText('surname', 'Jméno', 30, 255)
             ->addRule(Form::FILLED, 'Zadejte jméno prosím.');

		$form->addText('address', 'Adresa', 30, 255)
             ->addRule(Form::FILLED, 'Zadejte adresu prosím.');

   	    $form->addText('phone', 'Telefon', 30, 18)
             ->addRule(Form::FILLED, 'Zadejte telefon prosím.');

		$form->addText('email', 'e-mail', 30, 255)
			 ->addCondition($form::FILLED)
			 	 ->addRule(Form::EMAIL, 'Prosím zadejte platnou emailovou adresu.');
		
	    $this_week = $form->addContainer('this_week');
        $next_week = $form->addContainer('next_week');
        
        foreach ($this->days as $day => $day_cz) {
            $this_week->addText($day, $day_cz)
            		  ->setAttribute('autocomplete', 'off');
            		  
			$next_week->addText($day, $day_cz)
            		  ->setAttribute('autocomplete', 'off');        
        }
        
        $form->addHidden('timestamp', time());
        
        $form->addSubmit('send', 'Odeslat');
		
		$form->onSuccess[] = $this->orderFormSubmit;
        return $form;
    }
    
    public function orderFormSubmit(Form $form) {
        $values = $form->getValues();
        $lunch_price = 89;
        $total_price = 0;
        $this_week = array();
        $next_week = array();
        $this_week_amount = 0;
        $next_week_amount = 0;
        
        // test zda byl formulář vygenerován před dedlineam a potvrzen po deadlinu
		$deadline_time = strtotime($this->deadline_time);
		$generated_time = $values->timestamp;
		$actual_time = time();
		
		// test zda byl formulář vygenerovám ve stejný týdem, chyba nastávala když byl formulář vygenerován v neděli a potvrzev v pondělí
		$generated_week = date("W", $generated_time);
		$actual_week = date("W", $actual_time);	
	
		if((($deadline_time < $actual_time) && ($deadline_time > $generated_time)) || ($generated_week != $actual_week)) {
	        $this->flashMessage('Platnost formuláře vypršela.<br />Vaše objednávka nebyla zpracována!', 'error');
	        $this->redirect('order');
	        $this->terminate();
		}
		
		
        $order_data = $this->orderData
        				   ->findData($values['surname'], $values['address'], $values['phone'], $values['email']);
        
        if($order_data) {
        	$data_id = $order_data->id;
        	$cartage_id = $order_data->cartage_id;
        }
        else {
	        $data_id = $this->orderData
     	   		 	  	    ->insert($values['surname'], $values['address'], $values['phone'], $values['email']);
							  		  
			$cartage_id = NULL;
        }
        
	    $lunchs = $this->lunch->getWeekLunchs();
            
        foreach ($values['this_week'] as $day => $amount) {
           if($amount == 0 || $amount == "")
               continue;
           
           $lunch = $this->lunch
                         ->findBy(array("lunch_date" => $this->lunch->getWeekDayDate($day, 0)))
                         ->fetch();
                       
           $this->order
                ->insert($data_id, 
                         $lunch->id,
                         $cartage_id,
                         $amount);              
           
           $this_week[$day]['abbr']  = $lunchs[$day]['abbr'];
           $this_week[$day]['preparation']  = $lunchs[$day]['preparation'];
           $this_week[$day]['amount']  = $amount;
           $this_week[$day]['price'] = $amount * $lunch_price;
           $this_week_amount += $amount;
           $total_price += $this_week[$day]['price'];
        }

        $lunchs_next_week = $this->lunch->getWeekLunchs(1);
        
        foreach ($values['next_week'] as $day => $amount) {
           if($amount == 0 || $amount == "")
               continue;
           
           $lunch = $this->lunch
                         ->findBy(array("lunch_date" => $this->lunch->getWeekDayDate($day, 1)))
                         ->fetch();
                       
           $this->order
                ->insert($data_id, 
                         $lunch->id,
                         $cartage_id,
                         $amount);              
                                        
           $next_week[$day]['abbr']  = $lunchs_next_week[$day]['abbr'];
           $next_week[$day]['preparation']  = $lunchs_next_week[$day]['preparation'];                      
           $next_week[$day]['amount']  = $amount;
           $next_week[$day]['price'] = $amount * $lunch_price;                      
           $next_week_amount += $amount;
           $total_price += $next_week[$day]['price'];
        }
        
        $email_template = new Nette\Templating\FileTemplate('../app/templates/Menu/order-email.latte');
        $email_template->registerFilter(new Nette\Latte\Engine);
        $email_template->registerHelperLoader('Nette\Templating\Helpers::loader');
        $email_template->setTranslator($this->translator);

        $email_template->surname = $values['surname'];
        $email_template->address = $values['address'];
        $email_template->phone = $values['phone'];
        $email_template->email = $values['email'];
        $email_template->week_title = $this->menu->getWeekTitle(0, $this->locale);
        $email_template->next_week_title = $this->menu->getWeekTitle(1, $this->locale);
        $email_template->this_week = $this_week;
        $email_template->next_week = $next_week;
        $email_template->this_week_amount = $this_week_amount;
        $email_template->next_week_amount = $next_week_amount;
        $email_template->total_price = $total_price;

        $email_template->locale = $this->locale;


        if($values['email'] != "") {
            $from = $values['email'];
            
            $mail = new Message;
            $mail->setFrom("Madan Móhan <mmrozvoz@gmail.com>")
                 ->addTo($values['email'])
                 ->setSubject($this->translator->translate("Potvrzení objednávky obědů"))
                 ->setHtmlBody($email_template);
                 
            $mailer = new SendmailMailer;
            $mailer->send($mail);
        }
        else {
            $from = "Madan Móhan <mmrozvoz@gmail.com>";
        }
        
        $mail = new Message;
        $mail->setFrom($from)
             ->addTo("mmrozvoz@gmail.com")
             ->setSubject("Potvrzení objednávky obědů: {$values['surname']} - {$values['address']}")
             ->setHtmlBody($email_template);
        
        $mailer = new SendmailMailer;
        $mailer->send($mail);    
        
        $mail = new Message;
        $mail->setFrom($from)
             ->addTo("rozvoz@madanmohan.cz")
             ->setSubject("Potvrzení objednávky obědů: {$values['surname']} - {$values['address']}")
             ->setHtmlBody($email_template);
             
        $mailer = new SendmailMailer;
        $mailer->send($mail);        
        
        $this->flashMessage('Vaše objednávka byla přijata. Děkujeme, dobrou chuť.', 'success');
        $this->redirect('order');
    }
}
