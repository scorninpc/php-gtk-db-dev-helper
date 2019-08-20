<?php

namespace dbHelper\View;

/**
 * 
 */
class startView extends \Fabula\Mvc\View
{
	/**
	 * Executa antes de criar a interface
	 */
	public function beforeInterfaceCreate()
	{

	}

	/** 
	 * Cria a interface
	 */
	public function createInterface()
	{
		// Toolbar
		$this->mainToolbar = new \GtkToolbar();

			// New
			$tlb_btnnew = new \GtkToolButton("");
			$tlb_btnnew->set_icon_name("document-new");
			$this->mainToolbar->insert($tlb_btnnew, -1);
			$tlb_btnnew->connect("clicked", [$this, "tlbNewClicked"]);

			// Refresh
			$tlb_btnrefresh = new \GtkToolButton("");
			$tlb_btnrefresh->set_icon_name("view-refresh");
			$this->mainToolbar->insert($tlb_btnrefresh, -1);

			// SQL
			$tlb_btnsql = new \GtkToolButton("");
			$tlb_btnsql->set_icon_name("applications-office");
			$this->mainToolbar->insert($tlb_btnsql, -1);
			$tlb_btnsql->connect("clicked", [$this, "tlbSqlClicked"]);

			$this->mainToolbar->insert(new \GtkSeparatorToolItem(), -1);

			// Config
			$tlb_btnconfig = new \GtkToolButton("");
			$tlb_btnconfig->set_icon_name("emblem-system");
			$this->mainToolbar->insert($tlb_btnconfig, -1);
			$tlb_btnconfig->connect("clicked", [$this, "tlbConfigClicked"]);

		// Paned
		$this->paned = new \GtkPaned(\GtkOrientation::HORIZONTAL);
		$this->paned->set_position(120);

		// Treeview
		$this->trvMain = new \GtkTreeView();
			$renderer = new \GtkCellRendererPixbuf();
			$column = new \GtkTreeViewColumn("", $renderer, "pixbuf", 0);
			$this->trvMain->append_column($column);

			$renderer = new \GtkCellRendererText();
			$column = new \GtkTreeViewColumn("", $renderer, "text", 1);
			$this->trvMain->append_column($column);

		$scroll = new \GtkScrolledWindow();
		$scroll->add($this->trvMain);
		$scroll->set_policy(\GtkPolicyType::AUTOMATIC, \GtkPolicyType::AUTOMATIC);
		$this->paned->add1($scroll);
		
		// Model
		$this->trvModel = new \GtkTreeStore(\GObject::TYPE_OBJECT, \GObject::TYPE_STRING);
		$this->trvMain->set_model($this->trvModel);
		$this->trvMain->set_level_indentation(15);
		$this->trvMain->set_show_expanders(FALSE);
		$this->trvMain->set_enable_tree_lines(TRUE);

		// Create notebook
		$this->ntb = new \GtkNotebook();
		$this->ntb->set_tab_pos(\GtkPositionType::TOP);
		$this->paned->add2($this->ntb);

		// VBox principal
		$main_box = new \GtkBox(\GtkOrientation::VERTICAL);
		$main_box->pack_start($this->mainToolbar, FALSE, FALSE);
		$main_box->pack_start($this->paned, TRUE, TRUE);

		// Create window
		$this->startForm = new \GtkWindow(\Gtk::WINDOW_TOPLEVEL);
		$this->startForm->add($main_box);
		$this->startForm->set_title("DB Dev Helper :: PHP-GTK3");

		// Connects
		$this->connects();
	}

	/**
	 * Cria os connects
	 */
	public function connects()
	{
		// Treeview 
		$this->trvMain->connect("button-press-event", [$this->getController(), "trvMain_buttonPress"]);
		$this->trvMain->connect("button-release-event", [$this->getController(), "trvMain_buttonRelease"]);

		// Window
		$this->startForm->connect("destroy", [$this->getController(), "startForm_onDestroy"]); 
	}

	/**
	 * Executa depois de criar a interface
	 */
	public function afterInterfaceCreate()
	{

	}

	/**
	 * Método que exibe a tela
	 */
	public function showInterface()
	{
		$this->startForm->show_all();
	}

}