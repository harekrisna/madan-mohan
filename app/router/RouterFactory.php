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
    	

		$router[] = new Route('objednat-obed', array(
			'presenter' => 'Menu',
			'action' => 'order',
			'locale' => 'cs'
		));

		$router[] = new Route('en/order-meal', array(
			'presenter' => 'Menu',
			'action' => 'order',
			'locale' => 'en'
		));

		$router[] = new Route('clanek/<id>', 'Articles:article');

		$router[] = new Route('<presenter>/<action>', array(
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
			'locale' => "cs"
		));

		$router[] = new Route('<presenter>/<action>', array(
			'presenter' => array(
				Route::VALUE => "Home",
				Route::FILTER_TABLE => array(
					'articles' => 'Articles',
					'contact' => 'Contact',
					'vegetable' => 'Vegetable',
					'brno-food' => 'Home',
				)
			),
			'action' => "default",
			'locale' => "en"
		));		

		return $router;
	}

}
