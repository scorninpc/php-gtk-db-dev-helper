<?php

namespace Fabula\Mvc;

class Controller
{
	private $view;

	/**
	 *
	 */
	public function __construct()
	{
		$currentControllerName = get_class($this);
		$currentViewName = str_replace("Controller", "View", $currentControllerName);

		$this->view = new $currentViewName($this);
		$this->beforeShow();
		$this->view->showInterface();
		$this->afterShow();
	}

	/**
	 *
	 */
	public function getView()
	{
		return $this->view;
	}

	/**
	 * Abstrações
	 */
	public function beforeShow() {}
	public function afterShow() {}
}