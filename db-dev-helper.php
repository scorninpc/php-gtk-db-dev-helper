<?php

// Define absolute path
defined("APPLICATION_PATH") || define("APPLICATION_PATH", dirname(__FILE__));

// Get lang
$lang = "en";
$lang = "ptbr";

require_once("langs.php");

/**
 * Application class
 */
class Application
{
	/**
	 * Construtor
	 */
	public function __construct()
	{
		// Create toolbar
		$this->IToolbar();

		// Paned
		$paned = new GtkPaned(GtkOrientation::HORIZONTAL); // GtkHPaned and GtkVPaned is deprecated
		// $paned->set_position(120);

		// Treeview
		$tree = new GtkTreeView();
			$renderer = new GtkCellRendererText();
			$column = new GtkTreeViewColumn("", $renderer, "text", 0);
			$tree->append_column($column);
		
		$model = new GtkListStore(GObject::TYPE_STRING);
		$model->append(["line 1"]); $model->append(["line 2"]); $model->append(["line 3"]);
		$model->append(["line 1"]); $model->append(["line 2"]); $model->append(["line 3"]);
		$model->append(["line 1"]); $model->append(["line 2"]); $model->append(["line 3"]);
		$model->append(["line 1"]); $model->append(["line 2"]); $model->append(["line 3"]);
		$model->append(["line 1"]); $model->append(["line 2"]); $model->append(["line 3"]);
		$tree->set_model($model);

		$scroll = new GtkScrolledWindow();
		$scroll->add($tree); // add_with_viewport is deprecated
		$scroll->set_policy(GtkPolicyType::AUTOMATIC, GtkPolicyType::AUTOMATIC);
		$paned->add1($scroll);

		// Create notebook
		$this->ntb = new GtkNotebook();
		$this->ntb->set_tab_pos(GtkPositionType::TOP);
		$paned->add2($this->ntb);

		
		$this->create_new_tab("GtkLabel.cpp");
		$this->create_new_tab("GtkLabel.h");
		$this->create_new_tab("main.cpp");
		$this->create_new_tab("main.h");

		// VBox
		$main_box = new GtkVBox();
		$main_box->pack_start($this->widget['mainToolbar'], FALSE, FALSE);
		$main_box->pack_start($paned, TRUE, TRUE);
		
		// $this->b->connect("clicked", [$this, "b_clicked"]);

		// Create window
		$this->widgets['mainWindow'] = new GtkWindow();
		$this->widgets['mainWindow']->set_default_size(800, 600);
		$this->widgets['mainWindow']->add($main_box);
		$this->widgets['mainWindow']->set_title("DB Dev Helper :: PHP-GTK3");

		// Connects
		$this->widgets['mainWindow']->connect("destroy", [$this, "GtkWindowDestroy"]);

		// $this->widgets['mainWindow']->set_interactive_debugging(TRUE);

		// Show all
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

	/**
	 * Create main toolbar
	 */
	public function IToolbar()
	{
		// Toolbar
		$this->widget['mainToolbar'] = new GtkToolbar();

		// New
		$tlb_btnnew = new GtkToolButton("");
		$tlb_btnnew->set_icon_name("document-new");
		$this->widget['mainToolbar']->insert($tlb_btnnew, -1);

		// Refresh
		$tlb_btnrefresh = new GtkToolButton("");
		$tlb_btnrefresh->set_icon_name("view-refresh");
		$this->widget['mainToolbar']->insert($tlb_btnrefresh, -1);

		// SQL
		$tlb_btnsql = new GtkToolButton("");
		$tlb_btnsql->set_icon_name("applications-office");
		$this->widget['mainToolbar']->insert($tlb_btnsql, -1);
	}


	// OLDER
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
		Gtk::main_quit();
	}
}

// Start application
$app = new Application();
Gtk::main();