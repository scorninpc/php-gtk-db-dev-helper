<?php

namespace Fabula\Application;

use Fabula\Application\Services;
// use Fabula\Mvc\Request;
// use Fabula\Mvc\Front;

/**
 * Procedimentos de inicialização da aplicação
 */
class Bootstrap
{
	/**
	 * Inicializa a aplicação
	 */
	public function __construct($configFile=NULL)
	{
		// Inicia o autoloader
		spl_autoload_register([$this, "__autoloader"]);
		
		// Cria a configuração
		if($configFile) {
			$config = Config::createInstance($configFile);
		}
		
		// Adiciona a configuração ao serviços
		Services::getInstance()->addService("config", $config);
		
		// // Cria o front controller
		// $front = new Front();
		// Services::getInstance()->addService("front", $front);
		// $front->run();
	}
	
	/**
	 * Autoloader
	 */
	private function __autoloader($className)
	{
		// Percorre os paths para busca
		$paths = explode(":", ini_get('include_path'));

		foreach($paths as $path) {
			
			// Verifica se o arquivo existe com o nome do application
			$filename = $path . "/" . implode("/", $explods) . ".php";
			if (file_exists($filename)) {
				// Inclui o arquivo para o uso
				require_once ($filename);
				return TRUE;
			}

			// Verifica se o arquivo existe sem o nome do application
			$explods = explode("\\", $className);
			unset($explods[0]);
			$filename = $path . "/" . implode("/", $explods) . ".php";
			if (file_exists($filename)) {
				// Inclui o arquivo para o uso
				require_once ($filename);
				return TRUE;
			}
		}
		
		// @todo Adicionar tradução e FabulaException
		if(!class_exists($className)) {
			throw new \Exception("Classe $className não encontrada");
		}
	}

	/**
	 *
	 */
	public function run()
	{
		$config = Services::getInstance()->getService("config");

		$firstController = "\\" . $config->application->namespace . "\\Controller\\" . $config->application->start_form;
		
		$form = new $firstController();
		
		\Gtk::main();
	}
}