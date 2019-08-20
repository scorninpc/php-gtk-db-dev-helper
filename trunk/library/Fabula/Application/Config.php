<?php

namespace Fabula\Application;

/**
 * Classe para configuração
 */
class Config implements \Iterator
{

	private $position = 0;

	/**
	 * Instancia
	 *
	 * @var Config
	 */
	protected static $instance;

	/**
	 * Configuração
	 *
	 * @var Array
	 */
	protected $config;

	/**
	 * Construtor
	 *
	 * @param array $config Vetor com as configurações
	 */
	public function __construct($config = NULL)
	{
		$this->position = 0;
		$this->config = $config;
	}

	/**
	 * Singleton
	 */
	static public function createInstance($file)
	{
		if (! self::$instance) {
			$config = self::readConfig($file);
			
			$class = __CLASS__;
			self::$instance = new $class($config);
		}
		
		return self::$instance;
	}

	/**
	 * Efetua a leitura da configuração
	 */
	static protected function readConfig($filename)
	{
		if (! file_exists($filename)) {
			// @todo Adicionar tradução
			throw new \Exception("Arquivo de configuração não encontrado");
		}
		return include ($filename);
	}

	/**
	 * Recupera uma configuração
	 *
	 * @param string $param Nome do parametro
	 * @return mixed
	 */
	public function __get($param)
	{
		if (! isset($this->config[$param])) {
			// @todo Adicionar tradução
			throw new \Exception("Config {$param} not found");
		}
		
		if (is_array($this->config[$param])) {
			return new Config($this->config[$param]);
		}
		else {
			return $this->config[$param];
		}
	}
	
	/**
	 * Faz o merge de configurações
	 */
	public function mergeConfig($file)
	{
		if(!file_exists($file)) {
			// @todo Adicionar tradução
			throw new \Exception("Config file {$file} not found");
		}
		
		// Le o arquivo de configuração
		$toMerge = $this->readConfig($file);
		
		// Adiciona a nova configuração
		$this->config = array_replace_recursive ($this->config, $toMerge);
	}

	/**
	 * Implementação do Iterator
	 */
	function rewind()
	{
		return reset($this->config);
	}

	/**
	 * Implementação do Iterator
	 */
	function current()
	{
		return current($this->config);
	}

	/**
	 * Implementação do Iterator
	 */
	function key()
	{
		return key($this->config);
	}

	/**
	 * Implementação do Iterator
	 */
	function next()
	{
		return next($this->config);
	}

	/**
	 * Implementação do Iterator
	 */
	function valid()
	{
		return key($this->config) !== null;
	}
}
