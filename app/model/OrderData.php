<?php

namespace App\Model;
use Nette;
use Nette\Diagnostics\Debugger; 

class OrderData extends Table   {
	protected $tableName = 'order_data'; 
	
    public function insert($name, $address, $phone, $email, $cartage_id = NULL)	{
        $new_row = $this->getTable()
                	    ->insert(array('id' => NULL,
                    	               'person_name' => $name,
									   'address' => $address,
									   'phone' => str_replace(" ", "", $phone),
									   'email' => $email,
									   'cartage_id' => $cartage_id));
		
		return $new_row->id;									   
    }

    public function update($id, $data) {
        if(isset($data["cartage_id"]) && $data["cartage_id"] == "")
            $data["cartage_id"] = NULL;
            
        return $this->getTable()
        			->where(array("id" => $id))
        			->update($data);
    }
    
    public function findData($name, $address, $phone, $email) {
	    $order_data = $this->findBy(array("person_name" => $name,
        								  "address" => $address,
										  "phone" => str_replace(" ", "", $phone),
										  "email" => $email))
						   ->fetch();

        if($order_data)
        	return $order_data;
        else 
        	return false;
    }
}