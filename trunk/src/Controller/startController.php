<?php

namespace dbHelper\Controller;

/**
 *
 */
class startController extends \Fabula\Mvc\Controller
{
	protected $config = [];

	/**
	 * Executa antes de mostrar a tela
	 */
	public function beforeShow()
	{
		// Read config
		$this->config = json_decode(file_get_contents(APPLICATION_PATH . "/config.json"), TRUE);

		// Set default values for config
		if(!isset($this->config['panel_width'])) {
			$this->config['panel_width'] = 300;
		}
		if(!isset($this->config['window_maximized'])) {
			$this->config['window_maximized'] = TRUE;
		}
		if(!isset($this->config['window_width'])) {
			$this->config['window_width'] = 800;
			$this->config['window_height'] = 600;
		}
		if(!isset($this->config['window_top'])) {
			$this->config['window_top'] = -1;
			$this->config['window_left'] = -1;
		}
		if((!isset($this->config['servers_config_path'])) || (!file_exists($this->config['servers_config_path']))) {
			$this->config['servers_config_path'] = APPLICATION_PATH . "/servers.json";
			touch($this->config['servers_config_path']);
		}

		// Set window size
		$this->getView()->startForm->set_default_size($this->config['window_width'], $this->config['window_height']);

		// Set panel size, window size and position
		if($this->config['window_maximized']) {
			$this->getView()->startForm->maximize();
		}

		// Set window position
		$this->getView()->startForm->move($this->config['window_top'], $this->config['window_left']);
	}

	/**
	 * Executa depois de mostrar a tela
	 */
	public function afterShow()
	{
		// Set panel position
		$this->getView()->paned->set_position($this->config['panel_width']);
	}

	/*
	 *
	 */
	public function startForm_onDestroy($widget)
	{
		
		// Get panel width, window size and position
		$this->config['panel_width'] = $this->getView()->paned->get_position();
		$this->config['window_maximized'] = $this->getView()->startForm->is_maximized();

		list($width, $height) = $this->getView()->startForm->get_size();
		$this->config['window_width'] = $width;
		$this->config['window_height'] = $height;

		list($top, $left) = $a = $this->getView()->startForm->get_position();
		$this->config['window_top'] = $top;
		$this->config['window_left'] = $left;

		// Save the config
		file_put_contents(APPLICATION_PATH . "/config.json", json_encode($this->config));

		// Quit
		\Gtk::main_quit();
	}

}