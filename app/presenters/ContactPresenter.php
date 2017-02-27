<?php

namespace App\Presenters;
use Nette\Application\UI\Form;
use Nette,
	App\Model;
use Nette\Diagnostics\Debugger;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;


class ContactPresenter extends BasePresenter {

	public function renderDefault() {
    
	}
	
    protected function createComponentContactForm(){
	   	$form = new Nette\Application\UI\Form();
	    
	    $form->addText('name', 'Jméno', 30, 255)
       	     ->setEmptyValue('Jméno')
             ->addRule(Form::FILLED, 'Zadejte jméno prosím.');
             
		$form->addText('email', 'e-mail:', 30, 255)
			 ->setEmptyValue('e-mail')
             ->addRule(Form::EMAIL, 'Prosím zadejte platnou emailovou adresu.');

        $form->addTextArea('text', 'Vaše zpráva:', 45, 7)
             ->setEmptyValue('Vaše zpráva')
             ->addRule(Form::FILLED, 'Napište něco prosím.');
             
        $form->addSubmit('send', 'Odeslat');
		
		$form->onSuccess[] = $this->contactFormSubmit;
        return $form;
    }
    
    public function contactFormSubmit(Form $form) {
        $values = $form->getValues();
        
        $email_template = new Nette\Templating\FileTemplate('../app/templates/Contact/contact-email.latte');
        $email_template->registerFilter(new Nette\Latte\Engine);
        $email_template->registerHelperLoader('Nette\Templating\Helpers::loader');
        $email_template->name = $values['name'];
        $email_template->email = $values['email'];
        $email_template->text = $values['text'];

        $mail = new Message;
        $mail->setFrom($values['name']." <".$values['email'].">")
             ->addTo("Madan Móhan <mmrozvoz@gmail.com>")
             ->addBcc("Bart <bh.majkl@gmail.com>")
             ->setSubject("Nový komentář")
             ->setHtmlBody($email_template);
        
        $mailer = new SendmailMailer;
        $mailer->send($mail);
        $this->flashMessage('Váš komentář byl odeslán. Děkujeme.', 'success');
        $this->redirect('default');
    }
}

