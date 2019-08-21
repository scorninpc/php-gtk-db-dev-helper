<?php

namespace dbHelper\Controller;

/**
 *
 */
class connectionsController extends \Fabula\Mvc\Controller
{
	
	/**
	 * Executa antes de mostrar a tela
	 */
	public function beforeShow()
	{
		
	}

	/**
	 * Executa depois de mostrar a tela
	 */
	public function afterShow()
	{
		
	}

	/*
	 *
	 */
	public function startForm_onDestroy($widget)
	{
		
	}

	/**
	 *
	 */
	public function run()
	{
		$return = NULL;

		$run = $this->getView()->dialog->run();
		if($run == \GtkResponseType::OK) {
			// Return fields
			$name = $this->getView()->name->get_text();
			$host = $this->getView()->host->get_text();
			$username = $this->getView()->username->get_text();
			$password = $this->getView()->password->get_text();
			$database = $this->getView()->database->get_text();

			// Return values
			$return = [
				'name' => $name,
				'host' => $host,
				'username' => $username,
				'password' => $password,
				'database' => $database,
			];
		}

		$this->getView()->dialog->destroy();

		return $return;
	}

}