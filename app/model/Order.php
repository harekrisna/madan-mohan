<?php

namespace App\Model;
use Nette;
use Nette\Diagnostics\Debugger; 

class Order extends Table   {
	protected $tableName = 'order'; 

    public function insert($data_id, $lunch_id, $cartage_id, $count)	{
        return $this->getTable()
                    ->insert(array('id' => NULL,
								   'order_data_id' => $data_id,
                                   'lunch_id' => $lunch_id,
								   'cartage_id' => $cartage_id,
                                   'lunch_count' => $count));
    }
    
    public function update($id, $data) {
        if(isset($data["cartage_id"]) && $data["cartage_id"] == "")
            $data["cartage_id"] = NULL;
            
        return $this->getTable()
        			->where(array("id" => $id))
        			->update($data);
    }
}