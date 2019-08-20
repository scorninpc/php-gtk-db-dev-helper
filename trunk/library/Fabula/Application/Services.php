<?php
namespace Fabula\Application;

/**
 * Gerencia os serviços para torna-los acessivel à toda aplicação
 */
class Services
{

	/**
	 * Armazena a instancia
	 */
	protected static $instance = null;
	
	/**
	 * Armazena os serviços
	 */
	protected $_services = array();

	/**
	 * Cancela a inicialização direta
	 */
	protected function __construct()
	{}

	/**
	 * Protege clonagem
	 */
	protected function __clone()
	{}

	/**
	 * Recuepra a instancia
	 */
	public static function getInstance()
	{
		if (! isset(static::$instance)) {
			static::$instance = new static();
		}
		return static::$instance;
	}
	
	/**
	 * Seta um serviço
	 *
	 * @param $name String Nome do serviço
	 * @param $service String Objeto do serviço
	 */
	public function addService($name, $service)
	{
		// Verifica se existe algum serviço com o mesmo nome
		if (isset($this->_services[$name])) {
			// @todo Adicionar tradução
			throw new \Exception("Serviço com o mesmo nome já está em utilização");
		}
		
		// Adiciona o serviço
		$this->_services[$name] = $service;
		
		// Retorna o proprio objeto
		return $this;
	}

	/**
	 * Recupera um serviço
	 * 
	 * @param $name String Nome do serviço
	 */
	public function getService($name)
	{
		// Verifica se o serviço existe
		if (!isset($this->_services[$name])) {
			
			// @todo Adicionar tradução
			throw new \Exception("Serviço $name não está disponivel");
		}
		
		// Retorna o serviço
		return $this->_services[$name];
	}
}