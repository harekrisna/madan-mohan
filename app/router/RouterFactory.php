<?php

namespace App;

use Nette,
	Nette\Application\Routers\RouteList,
	Nette\Application\Routers\Route,
	Nette\Application\Routers\SimpleRouter;

/**
 * Router factory.
 */
class RouterFactory
{

	/**
	 * @return \Nette\Application\IRouter
	 */
	public function createRouter()
	{
		$router = new RouteList();	
		
    	# AdminModule routes
    	$router[] = new Route('admin1896/<presenter>/<action>[/<id>]', array(
            'module'    => 'Admin',
            'presenter' => 'Menu',
            'action'    => 'menu',
            'id'        => null
    	));		
    	

		$router[] = new Route('objednat-obed', 'Menu:order');		
		$router[] = new Route('clanek/<id>', 'Articles:article');
		
		$router[] = new Route('<presenter>/<action>[/<id>]', array(
			'presenter' => array(
				Route::VALUE => "Home",
				Route::FILTER_TABLE => array(
					'clanky' => 'Articles',
					'kontakt' => 'Contact',
					'zelenina' => 'Vegetable',
					'rozvoz-jidel-brno' => 'Home',
				)
			),
			'action' => "default",
		));

		return $router;
	}

}
