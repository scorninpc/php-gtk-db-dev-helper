<?php

namespace dbHelper\Controller;

/**
 *
 */
class startController extends \Fabula\Mvc\Controller
{
	protected $config = [];

	protected $servers = [];

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

		// Read the servers
		$this->servers = json_decode(file_get_contents($this->config['servers_config_path']), TRUE);

		// Read server list, set as disconnected and add to treeviews
		foreach($this->servers as $index => $server) {
			$this->servers[$index]['connected'] = FALSE; 
			$this->addServerToTreeview($this->servers[$index]);
		}
	}

	/**
	 * Executa depois de mostrar a tela
	 */
	public function afterShow()
	{
		// Set panel position
		$this->getView()->paned->set_position($this->config['panel_width']);

		// Verify if has servers in the config
		if(count($this->servers) == 0) {
			$this->mainToolbar_activate($this->getView()->mainToolbar->get_nth_item(0));
		}
	}

	/**
	 *	Button press on treeview
	 */
	public function trvMain_buttonPress($widget, $event)
	{
		// Double click
		if($event->type == \Gdk::_2BUTTON_PRESS) {
			// Get the model of treeview
			$model = $widget->get_model();

			// Return the selection and the iter of selected
			$selection = $widget->get_selection();
			$iter = $selection->get_selected($model);
			$path = $model->get_path($iter);

			$controller = $model->get_value($iter, 2);

			// Double clicked on server item
			if($controller == 1) {
				$this->doubleClickedServer($path, $iter, $model);
			}

			// Double clicked on database item
			else if($controller == 2) {
				$this->doubleClickedDatabase($path, $iter, $model);
			}

		}
	}

	/**
	 * Add new server toolbutton
	 */
	public function mainToolbar_activate($widget)
	{
		// Run dialog to new connection
		$connection = new connectionsController();
		$server = $connection->run();
		if(is_array($server)) {
			// Add the server for server list file and the treeview
			$server['connected'] = FALSE;
			$this->servers[] = $server;
			$this->addServerToTreeview($server);
		}
	}

	/**
	 * On form closed
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

		// Save the servers
		file_put_contents($this->config['servers_config_path'], json_encode($this->servers));

		// Quit
		\Gtk::main_quit();
	}

	/**
	 * Add server to a treeview
	 */
	public function addServerToTreeview($server)
	{
		// Create the pixbub
		$pixbuf = \GdkPixbuf::new_from_file_at_size(APPLICATION_PATH . "/assets/icons/server1.png", 14, -1);

		// Add to the treeview
		$iter = $this->getView()->trvModel->append(NULL, [$pixbuf, $server['name'], 1]); // 1 for servers controller
	}

	/**
	 * Add database to a treeview
	 */
	public function addDatabaseToTreeview($database, $iter)
	{
		var_dump($database);

		// Create the pixbub
		$pixbuf = \GdkPixbuf::new_from_file_at_size(APPLICATION_PATH . "/assets/icons/database.png", 14, -1);

		// Add to the treeview
		$iter = $this->getView()->trvModel->append($iter, [$pixbuf, $database, 2]); // 2 for database controller
	}

	/**
	 * Double click on treeview server item
	 */
	public function doubleClickedServer($path, $iter, $model)
	{
		// Verify if server are connected
		if($this->servers[$path]['connected'] === TRUE) {

			// Expand or collapse row
			if($this->getView()->trvMain->row_expanded($path))
				$this->getView()->trvMain->collapse_row($path);
			else
				$this->getView()->trvMain->expand_row($path);

			return false;
		}

		// Create the connection - Abstract for new types
		$host = $this->servers[$path]['host'];
		$username = $this->servers[$path]['username'];
		$password = $this->servers[$path]['password'];
		$database = $this->servers[$path]['database'];
		$dsn = "pgsql:host=$host;port=5432;dbname=$database;user=$username;password=$password";
		$this->servers[$path]['connection'] = new \PDO($dsn); // Verify if can connect and if not, show dialog

		// Retreave databases
		$databases = getAllDatabases($this->servers[$path]['connection']);
		foreach($databases as $database) {
			$this->addDatabaseToTreeview($database, $iter);
		}
		$this->getView()->trvMain->expand_row($path);

		// Change icon
		$pixbuf = \GdkPixbuf::new_from_file_at_size(APPLICATION_PATH . "/assets/icons/server2.png", 14, -1);
		$this->getView()->trvModel->set_value($iter, 0, $pixbuf);

		$this->servers[$path]['connected'] = TRUE;
	}

	/**
	 *
	 */
	public function doubleClickedDatabase($path, $iter, $model) 
	{
		
	}

}


// Abstract
function getAllDatabases($connection)
{
	$query = "SELECT datname FROM pg_database WHERE datistemplate = false;";
	$result = $connection->query($query);

	$databases = [];
	foreach ($result as $row) {
		$databases[] = $row['datname'];
	}

	sort($databases);

	return $databases;
}

function getAllShemas($connection)
{
	$exclude = ["pg_toast", "pg_temp_1", "pg_toast_temp_1", "information_schema", "pg_catalog"];

	$query = "select schema_name from information_schema.schemata;";
	$result = $connection->query($query);

	$schemas = [];
	foreach ($result as $row) {
		if(in_array($row['schema_name'], $exclude)) {
			continue;
		}
		$schemas[] = $row['schema_name'];
	}

	sort($schemas);

	return $schemas;
}