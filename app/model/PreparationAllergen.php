<?php

namespace App\Model;
use Nette;
use Tracy\Debugger;

class PreparationAllergen extends Table   {
	protected $tableName = 'preparation_allergen'; 	
    
    public function insert($data)	{
        return $this->getTable()->insert($data);
    }
}