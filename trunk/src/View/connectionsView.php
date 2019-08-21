<?php

namespace dbHelper\View;

/**
 * 
 */
class connectionsView extends \Fabula\Mvc\View
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
		$this->dialog = new \GtkDialog();

		$this->dialog->set_title("New server");
		// $this->dialog->set_transient_for($mainObject->widgets['mainWindow']);
		$this->dialog->set_size_request(400, 280);

		$this->dialog->add_button("Cancel", \GtkResponseType::CANCEL);
		$this->dialog->add_button("Save", \GtkResponseType::OK);

		// Create the box
		$vbox = new \GtkBox(\GtkOrientation::VERTICAL);

		// Create fields
		$label = new \GtkLabel("Name"); $label->set_xalign("0");
		$vbox->pack_start($label, TRUE, TRUE, 0);
		$this->name = new \GtkEntry();
		$vbox->pack_start($this->name, TRUE, TRUE, 0);

		$label = new \GtkLabel("Host"); $label->set_xalign("0");
		$vbox->pack_start($label, TRUE, TRUE, 0);
		$this->host = new \GtkEntry();
		$vbox->pack_start($this->host, TRUE, TRUE, 0);

		$label = new \GtkLabel("Username"); $label->set_xalign("0");
		$vbox->pack_start($label, TRUE, TRUE, 0);
		$this->username = new \GtkEntry();
		$vbox->pack_start($this->username, TRUE, TRUE, 0);

		$label = new \GtkLabel("Password"); $label->set_xalign("0");
		$vbox->pack_start($label, TRUE, TRUE, 0);
		$this->password = new \GtkEntry();
		$vbox->pack_start($this->password, TRUE, TRUE, 0);

		$label = new \GtkLabel("Database"); $label->set_xalign("0");
		$vbox->pack_start($label, TRUE, TRUE, 0);
		$this->database = new \GtkEntry();
		$vbox->pack_start($this->database, TRUE, TRUE, 0);

		// Add vbox
		$area = $this->dialog->get_content_area();
		$area->add($vbox);

		// Show all
		$area->show_all();
	}

	/**
	 * Cria os connects
	 */
	public function connects()
	{
		
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
		// $this->dialog->run();
	}

}