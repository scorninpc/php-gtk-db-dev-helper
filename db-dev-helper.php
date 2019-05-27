<?php

// Define absolute path
defined("APPLICATION_PATH") || define("APPLICATION_PATH", dirname(__FILE__));

// Get lang
$lang = "en";
$lang = "ptbr";

require_once("langs.php");
require_once("formConnection.php");
require_once("formConfigs.php");
require_once("DatabaseHelper.php");

/**
 * Application class
 */
class Application
{
	public $config;
	public $widgets;
	public $servers;

	const DISCONNECTED = 0;
	const CONNECTED = 1;

	/**
	 * Construtor
	 */
	public function __construct()
	{	

		// Read conf
		$this->config = json_decode(file_get_contents(APPLICATION_PATH . "/config.json"), TRUE);
		if(!isset($this->config['panel_width'])) {
			$this->config['panel_width'] = 300;
		}
		if(!isset($this->config['window_maximized'])) {
			$this->config['window_maximized'] = TRUE;
		}
		if((!isset($this->config['servers_config_path'])) || (!file_exists($this->config['servers_config_path']))) {
			$this->config['servers_config_path'] = APPLICATION_PATH . "/servers.json";
			touch($this->config['servers_config_path']);
		}

		// Read the servers
		$this->servers = json_decode(file_get_contents($this->config['servers_config_path']), TRUE);

		// Create toolbar
		$this->IToolbar();

		// Paned
		$this->widgets['paned'] = new GtkPaned(GtkOrientation::HORIZONTAL);
		$this->widgets['paned']->set_position($this->config['panel_width']);
		// $this->widgets['paned']->set_position(120);

		// Treeview
		$this->widgets['trvMain'] = new GtkTreeView();
			$renderer = new GtkCellRendererPixbuf();
			$column = new GtkTreeViewColumn("", $renderer, "pixbuf", 0);
			$this->widgets['trvMain']->append_column($column);

			$renderer = new GtkCellRendererText();
			$column = new GtkTreeViewColumn("", $renderer, "text", 1);
			$this->widgets['trvMain']->append_column($column);
		$this->widgets['trvMain']->connect("button-press-event", [$this, "trvMain_buttonPress"]);
		

		$selection = $this->widgets['trvMain']->get_selection();


		$scroll = new GtkScrolledWindow();
		$scroll->add($this->widgets['trvMain']);
		$scroll->set_policy(GtkPolicyType::AUTOMATIC, GtkPolicyType::AUTOMATIC);
		$this->widgets['paned']->add1($scroll);

		// Model
		$this->widgets['trvModel'] = new GtkTreeStore(GObject::TYPE_OBJECT, GObject::TYPE_STRING);
		$this->widgets['trvMain']->set_model($this->widgets['trvModel']);
		$this->widgets['trvMain']->set_level_indentation(15);
		$this->widgets['trvMain']->set_show_expanders(FALSE);
		$this->widgets['trvMain']->set_enable_tree_lines(TRUE);

		// Create notebook
		$this->ntb = new GtkNotebook();
		$this->ntb->set_tab_pos(GtkPositionType::TOP);
		$this->widgets['paned']->add2($this->ntb);

		
		$this->create_new_tab("GtkLabel.cpp");
		$this->create_new_tab("GtkLabel.h");
		$this->create_new_tab("main.cpp");
		$this->create_new_tab("main.h");

		// VBox
		$main_box = new GtkVBox();
		$main_box->pack_start($this->widgets['mainToolbar'], FALSE, FALSE);
		$main_box->pack_start($this->widgets['paned'], TRUE, TRUE);
		
		// $this->b->connect("clicked", [$this, "b_clicked"]);

		// Create window
		$this->widgets['mainWindow'] = new GtkWindow();
		$this->widgets['mainWindow']->set_default_size(800, 600);
		$this->widgets['mainWindow']->add($main_box);
		$this->widgets['mainWindow']->set_title("DB Dev Helper :: PHP-GTK3");

		// Connects
		$this->widgets['mainWindow']->connect("destroy", [$this, "GtkWindowDestroy"]);

		// $this->widgets['mainWindow']->set_interactive_debugging(TRUE);

		// Read server list and add to treeviews
		$servers = $this->servers;
		$this->servers = [];
		foreach($servers as $server) {
			$this->addServerToList($server);
		}

		// Show all
		if($this->config['window_maximized']) {
			$this->widgets['mainWindow']->maximize();
		}
		$this->widgets['mainWindow']->show_all();


		// Show dialog of alert
		$dialog = GtkMessageDialog::new_with_markup(
			$this->widgets['mainWindow'],
			GtkDialogFlags::MODAL,
			GtkMessageType::WARNING,
			GtkButtonsType::OK,
			_t("DB Dev Helper is a PHP-GTK3 application wrote to test PHP-GTK3 bind.")
		);
		$dialog->format_secondary_markup("<i>" . _t("Use carefully, and please let us know about problems in our community at") . " <a href=\"https://github.com/scorninpc/php-gtk3\">Github</a>.</i>");
		// $dialog->run();
		// $dialog->destroy();
	}

	public function trvMain_buttonPress($widget, $event)
	{
		if($event->type == Gdk::_2BUTTON_PRESS) {
			// Get the model of treeview
			$model = $widget->get_model();

			// Return the selection and the iter of selected
			$selection = $widget->get_selection();
			$iter = $selection->get_selected($model);
			$path = $model->get_path($iter);

			// Explode to get location of clicked
			$paths = explode(":", $path);

			//
			$host = $this->servers[$paths[0]]['host'];
			$username = $this->servers[$paths[0]]['username'];
			$password = $this->servers[$paths[0]]['password'];

			// Clicked on server
			if(count($paths) == 1) {
				// Verify connection
				if($this->servers[$path]['state'] == self::CONNECTED) {

					if($this->widgets['trvMain']->row_expanded($path)) {
						$this->widgets['trvMain']->collapse_row($path);
					}
					else {
						$this->widgets['trvMain']->expand_row($path);
					}

					return FALSE;
				}

				// database
				$database = $this->servers[$path]['database'];

				// Connect to database
				$dsn = "pgsql:host=$host;port=5432;dbname=$database;user=$username;password=$password";
				try {
					$conn = new PDO($dsn);
					if(!$conn) {
						// Not connected
						$dialog = GtkMessageDialog::new_with_markup($this->widgets['mainWindow'], GtkDialogFlags::MODAL, GtkMessageType::ERROR, GtkButtonsType::OK, _t("Cloud not connect to server"));
						$a = $dialog->run();
						$dialog->destroy();
					}
					else {
						// Set new icon
						$pixbuf = GdkPixbuf::new_from_file_at_size(APPLICATION_PATH . "/icons/server2.png", 14, -1);
						$this->widgets['trvModel']->set_value($this->servers[$path]['trvIter'], 0, $pixbuf);
						$this->servers[$path]['state'] = self::CONNECTED;

						// Connected
						$this->servers[$path]['connection'] = $conn;

						// Get all tables
						$databases = DatabaseHelper::getAllDatabases($this->servers[$path]['connection']);
						foreach($databases as $index => $database) {
							// Add database to list
							$pixbuf = GdkPixbuf::new_from_file_at_size(APPLICATION_PATH . "/icons/database.png", 14, -1);
							$iter = $this->widgets['trvModel']->append($this->servers[$path]['trvIter'], [$pixbuf, $database]);

							$this->servers[$path]['databases'][$index] = [
								'name' => $database,
								'trvIter'  => $iter,
								'state' => self::DISCONNECTED
							];
						}

						// Expand
						$this->widgets['trvMain']->expand_row($path);

					}
				}
				catch (PDOException $e) {
					// Show dialog
					$dialog = GtkMessageDialog::new_with_markup($this->widgets['mainWindow'], GtkDialogFlags::MODAL, GtkMessageType::ERROR, GtkButtonsType::OK, $e->getMessage());
					$a = $dialog->run();
					$dialog->destroy();
				}
			}

			// Clicked on database
			else if(count($paths) == 2) {
				$database = $this->servers[$paths[0]]['databases'][$paths[1]];
				$dbname = $database['name'];

				if($this->servers[$paths[0]]['databases'][$paths[1]]['state'] == self::CONNECTED) {

					if($this->widgets['trvMain']->row_expanded($path)) {
						$this->widgets['trvMain']->collapse_row($path);
					}
					else {
						$this->widgets['trvMain']->expand_row($path);
					}

					return FALSE;
				}

				// Connect to database
				$dsn = "pgsql:host=$host;port=5432;dbname=$dbname;user=$username;password=$password";
				
				try {
					$conn = new PDO($dsn);
					if(!$conn) {
						// Not connected
						$dialog = GtkMessageDialog::new_with_markup($this->widgets['mainWindow'], GtkDialogFlags::MODAL, GtkMessageType::ERROR, GtkButtonsType::OK, _t("Cloud not connect to database"));
						$a = $dialog->run();
						$dialog->destroy();
					}
					else {
						// Set new icon
						$pixbuf = GdkPixbuf::new_from_file_at_size(APPLICATION_PATH . "/icons/database1.png", 14, -1);
						$this->widgets['trvModel']->set_value($database['trvIter'], 0, $pixbuf);
						$this->servers[$paths[0]]['databases'][$paths[1]]['state'] = self::CONNECTED;

						// Connected
						$this->servers[$paths[0]]['databases'][$paths[1]]['connection'] = $conn;
						$schemas = DatabaseHelper::getAllShemas($conn);

						foreach($schemas as $index => $schema) {
							// Add schema to list
							$pixbuf = GdkPixbuf::new_from_file_at_size(APPLICATION_PATH . "/icons/table4.png", 14, -1);
							$iter = $this->widgets['trvModel']->append($database['trvIter'], [$pixbuf, $schema]);

							// Get tables of schema
							$tables = DatabaseHelper::getAllTables($conn, $schema);
							foreach($tables as $table) {
								// Add schema to list
								$pixbuf = GdkPixbuf::new_from_file_at_size(APPLICATION_PATH . "/icons/table1.png", 14, -1);
								$this->widgets['trvModel']->append($iter, [$pixbuf, $table]);
							}
						}

						// Expand
						$this->widgets['trvMain']->expand_row($path, FALSE);

					}
				}
				catch (PDOException $e) {
					// Show dialog
					$dialog = GtkMessageDialog::new_with_markup($this->widgets['mainWindow'], GtkDialogFlags::MODAL, GtkMessageType::ERROR, GtkButtonsType::OK, $e->getMessage());
					$a = $dialog->run();
					$dialog->destroy();
				}
			}

			// Clicked on schema
			else if(count($paths) == 3) {

				if($this->widgets['trvMain']->row_expanded($path)) {
					$this->widgets['trvMain']->collapse_row($path);
				}
				else {
					$this->widgets['trvMain']->expand_row($path);
				}

			}
		}
	}

	/**
	 * Create main toolbar
	 */
	public function IToolbar()
	{
		// Toolbar
		$this->widgets['mainToolbar'] = new GtkToolbar();

		// New
		$tlb_btnnew = new GtkToolButton("");
		$tlb_btnnew->set_icon_name("document-new");
		$this->widgets['mainToolbar']->insert($tlb_btnnew, -1);
		$tlb_btnnew->connect("clicked", [$this, "tlbNewClicked"]);

		// Refresh
		$tlb_btnrefresh = new GtkToolButton("");
		$tlb_btnrefresh->set_icon_name("view-refresh");
		$this->widgets['mainToolbar']->insert($tlb_btnrefresh, -1);

		// SQL
		$tlb_btnsql = new GtkToolButton("");
		$tlb_btnsql->set_icon_name("applications-office");
		$this->widgets['mainToolbar']->insert($tlb_btnsql, -1);

		// Config
		$tlb_btnconfig = new GtkToolButton("");
		$tlb_btnconfig->set_icon_name("emblem-system");
		$this->widgets['mainToolbar']->insert($tlb_btnconfig, -1);
		$tlb_btnconfig->connect("clicked", [$this, "tlbConfigClicked"]);
	}

	/**
	 *
	 */
	public function tlbConfigClicked($widget)
	{
		$a = new formConfigs($this);

		// Set the configs before open
		$a->widgets['servers_config_path']->set_text($this->config['servers_config_path']);

		// Run dialog
		$response = $a->run();
		if($response == GtkResponseType::OK) {
			$name = $a->widgets['servers_config_path']->get_text();
			$this->config['servers_config_path'] = $name;
		}

		$a->destroy();
	}


	/**
	 *
	 */
	public function tlbNewClicked($widget)
	{
		$a = new formConnection($this);
		$response = $a->run();
		if($response == GtkResponseType::OK) {
			$name = $a->widgets['name']->get_text();
			$host = $a->widgets['host']->get_text();
			$username = $a->widgets['username']->get_text();
			$password = $a->widgets['password']->get_text();
			$database = $a->widgets['database']->get_text();

			// Add the server to the treeview
			$this->addServerToList([
				'name' => $name,
				'host' => $host,
				'username' => $username,
				'password' => $password,
				'database' => $database,
			]);

			// Add the server for server list file
			$this->server[] = [
				'name' => $name,
				'host' => $host,
				'username' => $username,
				'password' => $password,
				'database' => $database,
			];
		}

		$a->destroy();
	}


	/**
	 *
	 */
	public function create_new_tab($label)
	{
		$hbox = new GtkHBox();
		$hbox->set_margin_start(5);
		$hbox->set_margin_end(5);

		$button_close = GtkButton::new_from_icon_name("gtk-close");
		$button_close->set_size_request(5, 5);
		$label = new GtkLabel($label);
		$hbox->pack_start($label, TRUE, TRUE, 10);
		// $hbox->pack_start($button_close, FALSE, FALSE);

		$text = new GtkTextView();
		$scroll = new GtkScrolledWindow();
		$scroll->add($text);
		$scroll->set_policy(GtkPolicyType::AUTOMATIC, GtkPolicyType::AUTOMATIC);

		$this->ntb->insert_page($scroll, $hbox);

		$button_close->connect("clicked", function() {

			// $dialog = GtkDialog::new_with_buttons("Titulo", $this->widgets['mainWindow'], GtkDialogFlags::MODAL);
			// $dialog->set_transient_for($this->widgets['mainWindow']);
			// $box = $dialog->get_content_area();
			// var_dump($box);
			// $h = new GtkHBox(30);
			// $h->set_margin_start(20);
			// $h->set_margin_end(20);
			// $h->set_margin_top(20);
			// $h->set_margin_bottom(20);
			// $h->pack_start(new GtkLabel("My dialog message, taokay?"), TRUE, TRUE, 30);
			// $box->pack_end($h, TRUE, TRUE, 30);
			// $box->show_all();

			// $a = $dialog->run();
			// if($a == GtkResponseType::OK) {
			// 	var_dump("OK");
			// }
			// else {
			// 	var_dump("ERRO");
			// }
			// $dialog->destroy();

			$filter = new GtkFileFilter();
			$filter->set_name("PHP Files");
			$filter->add_pattern("*.php");

			// File chooser
			$dialog = new GtkFileChooserDialog("Open file", $this->widgets['mainWindow'], GtkFileChooserAction::OPEN, [
				"Cancel", GtkResponseType::CANCEL,
				"Ok", GtkResponseType::OK,
			]);

			$filter = new GtkFileFilter();
			$filter->set_name("PHP Files");
			$filter->add_pattern("*.php");
			$dialog->add_filter($filter);

			$filter = new GtkFileFilter();
			$filter->set_name("HTML Files");
			$filter->add_pattern("*.html");
			$filter->add_pattern("*.tpl");
			$dialog->add_filter($filter);


			$dialog->set_select_multiple(FALSE);
			$a = $dialog->run();
			if($a == GtkResponseType::OK) {
				var_dump($dialog->get_filename());
			}
			$dialog->destroy();

		});

		$button_close->connect("clicked", [$this, "close_tab"], $hbox);

		$hbox->show_all();
	}

	public function close_tab($widget=NULL, $event=NULL, $child=NULL)
	{
		// $this->ntb->remove_page($page_num);

		// $num = $this->ntb->get_current_page();
		// $this->ntb->remove_page($num);
	}

	/**
	 * GtkWindow on Destroy
	 */
	public function GtkWindowDestroy($widget=NULL, $event=NULL)
	{
		// Get panel width
		$this->config['panel_width'] = $this->widgets['paned']->get_position();
		$this->config['window_maximized'] = $this->widgets['mainWindow']->is_maximized();

		list($width, $height) = $a = $this->widgets['mainWindow']->get_size();
		$this->config['window_width'] = $width;
		$this->config['window_height'] = $height;

		var_dump($a);

		// Save the config
		file_put_contents(APPLICATION_PATH . "/config.json", json_encode($this->config));

		// Save the server list
		$servers = $this->servers;
		foreach($servers as $index => $server) {
			unset($servers[$index]['trvIter']);
			unset($servers[$index]['state']);
			unset($servers[$index]['connection']);
			unset($servers[$index]['databases']);
		}
		file_put_contents($this->config['servers_config_path'], json_encode($servers));

		Gtk::main_quit();
	}

	/**
	 *
	 */
	public function addServerToList($row)
	{
		$pixbuf = GdkPixbuf::new_from_file_at_size(APPLICATION_PATH . "/icons/server1.png", 14, -1);

		// Add to the treeview
		$iter = $this->widgets['trvModel']->append(NULL, [$pixbuf, $row['name']]);

		// Save to the global list
		$this->servers[] = [
			'name' => $row['name'],
			'host' => $row['host'],
			'username' => $row['username'],
			'password' => $row['password'],
			'database' => $row['database'],
			'trvIter' => $iter,
			'state' => self::DISCONNECTED,
		];
	}

}

// Start application
$app = new Application();
Gtk::main();