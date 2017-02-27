<?php

namespace App\AdminModule\Presenters;
use Nette,
	App\Model;
use Nette\Diagnostics\Debugger;
use Nette\Application\UI\Form;

class PreparationPresenter extends BasePresenter {    

    /** @persistent int*/
    public $category_id;
    
    /** @persistent int*/
    public $preparation_id;

    protected function startup()  {
        parent::startup();
    
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
        
        if(empty($this->category_id)) {
   	        $category = $this->category
                             ->findAll()
                             ->fetch();
            
            $this->category_id = $category->id;
        }
    }  

    public function beforeRender() {
        $this->template->menu = array(
          "Preparation:list" => "Preparace",
          "Preparation:categoryList" => "Kategorie",
          "Allergen:list" => "Alergeny",
        );
    }

    protected function createComponentChooseCategoryForm()  {
	   	$form = new Nette\Application\UI\Form();

	    $categories = $this->category
                           ->findAll()
                           ->order('title');
                           
   	    $form->addSelect('category_id', 'Kategorie:', $categories->fetchPairs('id', 'title'))
             ->setAttribute('onchange', 'submit()')
             ->setValue($this->category_id);        
		
        $form->onSuccess[] = array($this, 'chooseCategorySubmitted');     
        return $form;
    }

    public function chooseCategorySubmitted(Nette\Application\UI\Form $form)    {
        $values = $form->getValues();
        $this->category_id = $values->category_id;
        $this->redirect('list');
    }
    
    public function actionList() {
   	    $preparations = $this->preparation
                             ->findAll()
                             ->order('title')
                             ->where("category_id = {$this->category_id}");
        
        $this->template->preparations = $preparations;
    }

    protected function createComponentPreparationForm(){
	   	$form = new Nette\Application\UI\Form();

	    $form->addText('title', 'Název:', 30, 255)
      	     ->setRequired('Zadejte prosím název.');

	    $categories = $this->category
                           ->findAll()
                           ->order('title');
                           
   	    $form->addSelect('category_id', 'Kategorie:', $categories->fetchPairs('id', 'title'))
      	     ->setPrompt('Kategorie:')
             ->setRequired('Zvolte prosím kategorii.');

           
        $form->addSubmit('insert', 'Uložit')
		     ->onClick[] = array($this, 'preparationFormInsert');

        $form->addSubmit('update', 'Uložit')
   		     ->onClick[] = array($this, 'preparationFormUpdate');
		     
        return $form;
    }
    
    public function preparationFormInsert(\Nette\Forms\Controls\SubmitButton $button)   {
        $form = $button->form;
        $values = $form->getValues();
        
        $ok = $this->preparation
                   ->insert($values->title, $values->category_id);
        
        if($ok)
            $this->flashMessage('Preparace byla přidána.', 'success');
        else 
            $this->flashMessage('Preparace již existuje.', 'info');
        
        $this->redirect('list');
    }
    
    public function preparationFormUpdate(\Nette\Forms\Controls\SubmitButton $button)
    {
        $form = $button->form;
        $values = $form->getValues(); 
        
        $this->preparation
             ->update($this->preparation_id, $values);
        
        $this->preparation_id = "";
        $this->flashMessage('Preparace byla aktualizována.', 'success');
        $this->redirect('list');
    }

    protected function createComponentAllergensForm(){
	   	$form = new Nette\Application\UI\Form();
	   	
	   	$allergens = $this->allergen->findAll()
	   								->order('title')
	   								->fetchPairs('id', 'title');
	   	
		$form->addCheckboxList('allergens', 'Alergeny:', $allergens);		     
        return $form;
    }

    public function actionEditPreparation($preparation_id) {
        $preparations = $this->preparation
                             ->findAll()
                             ->order('title')
                             ->where("category_id = {$this->category_id}");
        
        $this->template->preparations = $preparations;
        
        $preparation = $this->preparation
                            ->find($preparation_id);
        
        $this["preparationForm"]->setDefaults($preparation);
       	
       	$checked = array();
       	       	 
        foreach($preparation->related("preparation_allergen")->fetchPairs("id") as $id => $object) {
        	$checked[] = $id;
        }
        
        $this["allergensForm"]["allergens"]->setDefaultValue($checked);
        $this->preparation_id = $preparation_id;
        $this->template->preparation_id = $preparation_id;
    }
    
    public function handleRemovePreparation($preparation_id) {
        try{
            $this->preparation
                 ->delete($preparation_id);
            $this->flashMessage('Preparace byla odstraněna.', 'success');    
            $this->redirect('list');
            
        }catch(\PDOException $e){
            if($e->getCode() == 23000)
                $this->flashMessage('Nelze odstranit preparaci, dokud je použita v menu.', 'error');
            else
                throw $e;
        }
    }
    
    public function actionCategoryList() {
   	    $categories = $this->category
                            ->findAll()
                            ->order('title');
        
        $this->template->categories = $categories;
    }

    public function actionEditCategory($category_id) {
        $categories = $this->category
                           ->findAll()
                           ->order('title');
        
        $this->template->categories = $categories;
        
        $category = $this->category
                         ->find($category_id);
        
        $this["categoryForm"]->setDefaults($category);
        $this->category_id = $category_id;  
    }

    protected function createComponentCategoryForm(){
	   	$form = new Nette\Application\UI\Form();

	    $form->addText('title', 'Název:', 30, 255)
      	     ->setRequired('Zadejte prosím název kategorie.');
           
        $form->addSubmit('insert', 'Uložit')
		     ->onClick[] = array($this, 'categoryInsert');

        $form->addSubmit('update', 'Uložit')
   		     ->onClick[] = array($this, 'categoryUpdate');
		     
        return $form;
    }
    
    public function handleRemoveCategory($id) {
        try{
            $this->category
                 ->delete($id);

            $this->flashMessage('Kategorie byla odstraněna.', 'success');    
            
        }catch(\PDOException $e){
            if($e->getCode() == 23000)
                $this->flashMessage('Nelze odstranit kategorii, dokud obshuje preparace.', 'error');
            else
                throw $e;
        }       

        $this->redirect('categoryList');
    }
    
    public function categoryInsert(\Nette\Forms\Controls\SubmitButton $button)
    {
        $form = $button->form;
        $values = $form->getValues();
        
        $this->category
             ->insert($values->title);

        
        $this->flashMessage('Kategorie byla přidána.', 'success');
        $this->redirect('categoryList');
    }
    
    public function categoryUpdate(\Nette\Forms\Controls\SubmitButton $button)
    {
        $form = $button->form;
        $values = $form->getValues();    
        
        $this->category
             ->update($this->category_id, $values);
        
        $this->preparation_id = "";
        $this->flashMessage('Kategorie byla aktualizována.', 'success');
        $this->redirect('categoryList');
    }    
    
    public function handleSetAllergenToPreparation($preparation_id, $allergen_id) {
		$this->preparationAllergen->insert(array("preparation_id" => $preparation_id,
												 "allergen_id" => $allergen_id,
										  ));
		$this->sendPayload();
    }
    
    public function handleUnsetAllergenToPreparation($preparation_id, $allergen_id) {
		$this->preparationAllergen->findBy(array("preparation_id" => $preparation_id,
								  				"allergen_id" => $allergen_id,
								  		  ))
								  ->delete();
								
		$this->sendPayload();
    }
}
