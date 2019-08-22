<?php

namespace dbHelper\Controller;

/**
 *
 */
class startController extends \Fabula\Mvc\Controller
{
	protected $config = [];

	protected $servers = [];

	protected $connects = [];

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
		$this->getView()->trvModel->append(NULL, [$pixbuf, $server['name'], 1]); // 1 for servers controller
	}

	/**
	 *
	 */
	public function connectToDatabase($path, $host, $username, $password, $database, $iter=NULL)
	{
		// Verify if can connect and if not, show dialog
		try {
			$this->connects[$path][$database] = new \dbHelper\Helpers\Database($host, $database, $username, $password);
		}
		catch(\Exception $e) {
			// Not connected
			$dialog = \GtkMessageDialog::new_with_markup($this->widgets['mainWindow'], \GtkDialogFlags::MODAL, \GtkMessageType::ERROR, \GtkButtonsType::OK, $e->getMessage());
			$a = $dialog->run();
			$dialog->destroy();

			return false;
		}

		// Change database connected icon
		$pixbuf = \GdkPixbuf::new_from_file_at_size(APPLICATION_PATH . "/assets/icons/database1.png", 14, -1);
		$this->getView()->trvModel->set_value($iter, 0, $pixbuf);


		return true;
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
			else {
				$this->getView()->trvMain->expand_row($path);
			}

			return false;
		}

		// Create the connection
		$host = $this->servers[$path]['host'];
		$username = $this->servers[$path]['username'];
		$password = $this->servers[$path]['password'];
		$database = $this->servers[$path]['database'];
		$test = $this->connectToDatabase($path, $host, $username, $password, $database, $iter);
		if(!$test) {
			return false;
		}

		// Retreave databases
		$databases = $this->connects[$path][$database]->getDatabases();

		// Add to treeview
		foreach($databases as $row) {
			// Create the pixbub
			$pixbuf = \GdkPixbuf::new_from_file_at_size(APPLICATION_PATH . "/assets/icons/database.png", 14, -1);
			$atual_iter = $model->append($iter, [$pixbuf, $row, 2]); // 2 for database controller

			// Save the atual iter of database
			if($row == $database) {
				$current_iter = $atual_iter;
			}
		}
		$this->getView()->trvMain->expand_row($path);

		// Change icon
		$pixbuf = \GdkPixbuf::new_from_file_at_size(APPLICATION_PATH . "/assets/icons/database1.png", 14, -1);
		$this->getView()->trvModel->set_value($current_iter, 0, $pixbuf);

		// Set as connected
		$this->servers[$path]['connected'] = TRUE;


// Verify to abstract database connect and change icon to set database connected

		// Retreave schemas
		$schemas = $this->connects[$path][$database]->getSchemas($database);
		
		// Add to treeview
		foreach($schemas as $schema) {
			// Create the pixbub
			$pixbuf = \GdkPixbuf::new_from_file_at_size(APPLICATION_PATH . "/assets/icons/database1.png", 14, -1);
			$model->append($current_iter, [$pixbuf, $schema, 3]); // 3 for schemas controller
		}

		$this->getView()->trvMain->expand_row($path = $model->get_path($current_iter));
	}

	/**
	 *
	 */
	public function doubleClickedDatabase($path, $iter, $model) 
	{
		$paths = explode(":", $path);

		// Get database name selected and verify if ar connected
		$database = $model->get_value($iter, 1);
		if(!isset($this->connects[$paths[0]][$database])) {

			// Create the connection
			$host = $this->servers[$paths[0]]['host'];
			$username = $this->servers[$paths[0]]['username'];
			$password = $this->servers[$paths[0]]['password'];
			$test = $this->connectToDatabase($paths[0], $host, $username, $password, $database, $iter);
			if(!$test) {
				return false;
			}

			// Retreave schemas
			$schemas = $this->connects[$paths[0]][$database]->getSchemas($database);
			
			// Add to treeview
			foreach($schemas as $schema) {
				// Create the pixbub
				$pixbuf = \GdkPixbuf::new_from_file_at_size(APPLICATION_PATH . "/assets/icons/database1.png", 14, -1);
				$this->getView()->trvModel->append($iter, [$pixbuf, $schema, 3]); // 3 for schemas controller
			}

			$this->getView()->trvMain->expand_row($path);
		}
		else {
			// Expand or collapse row
			if($this->getView()->trvMain->row_expanded($path))
				$this->getView()->trvMain->collapse_row($path);
			else
				$this->getView()->trvMain->expand_row($path);

			return false;
		}
	}

}