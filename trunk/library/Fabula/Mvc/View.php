<?php

namespace Fabula\Mvc;

class View
{
	private $controller;

	/**
	 *
	 */
	public function __construct($controller)
	{
		$this->controller = $controller;

		$this->beforeInterfaceCreate();

		$this->createInterface();

		$this->afterInterfaceCreate();
	}

	/**
	 *
	 */
	public function getController()
	{
		return $this->controller;
	}

	/**
	 * Abstrações
	 */
	public function beforeInterfaceCreate() {}
	public function createInterface() {}
	public function afterInterfaceCreate() {}
	public function showInterface() {}
}