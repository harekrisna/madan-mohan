<?php

namespace App\Model;
use Nette;
use Nette\Diagnostics\Debugger; 

class Menu extends Nette\Object   {
    public function getWeekTitle($offset = 0, $lang = "cs") {
        if(date("l") == "Sunday")
            $offset--;

        if($lang == 'cs') {
        	$week_title = "týden";
        	$from_date_format = "j.n";
        	$to_date_format = "j.n.Y";
        }
        else {
        	$week_title = "week";
        	$from_date_format = "jS M";
        	$to_date_format = "jS M Y";
        }

		$from = date($from_date_format, strtotime("monday this week {$offset} week"));
        $to = date($to_date_format, strtotime("friday this week {$offset} week"));
        $week_number = number_format(date("W", strtotime("monday this week {$offset} week")));

        $week_title = "{$week_number}. {$week_title} ({$from} - {$to})";
        
        return $week_title;
    }
}