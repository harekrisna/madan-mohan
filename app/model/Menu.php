<?php

namespace App\Model;
use Nette;
use Nette\Diagnostics\Debugger; 

class Menu extends Nette\Object   {
    public function getWeekTitle($offset = 0) {
        if(date("l") == "Sunday")
            $offset--;
       	$from = date("j.n", strtotime("monday this week {$offset} week"));
        $to = date("j.n.Y", strtotime("friday this week {$offset} week"));
        $week_number = number_format(date("W", strtotime("monday this week {$offset} week")));
        
        $week_title = "Týden {$week_number}. ({$from}. - {$to})";
        
        return $week_title;
    }
}