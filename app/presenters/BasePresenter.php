<?php

namespace App\Presenters;

use Nette,
	App\Model;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	public $oldLayoutMode = FALSE;
	public $oldModuleMode = FALSE;
    protected $menu;
    protected $order;
    protected $orderData;
    protected $lunch;


	
	protected function beforeRender()
	{
		$this->template->viewName = $this->view;

		$a = strrpos($this->name, ':');
		if ($a === FALSE) {
			$this->template->moduleName = '';
			$this->template->presenterName = $this->name;
		} else {
			$this->template->moduleName = substr($this->name, 0, $a + 1);
			$this->template->presenterName = substr($this->name, $a + 1);
		}
	}

  protected function startup()	{
		parent::startup();
/*		
		$container = $this->presenter->context->getService("container");
		$httpRequest = $container->getByType('Nette\Http\Request');
		$host = $httpRequest->getUrl()->getHost();
		if($host == "www.sktscore.cz" || $host == "sktscore.cz") {
			$this->redirectUrl("http://www.madanmohan.cz");
		}
*/				
        $this->menu = $this->context->menu;
        $this->order = $this->context->order;
        $this->orderData = $this->context->orderData;
        $this->lunch = $this->context->lunch;
  }
}
