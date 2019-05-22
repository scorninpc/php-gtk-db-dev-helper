<?php

/**
 * Form with connection info
 */
class formConnection extends GtkDialog
{
	public function __construct($mainObject) 
	{
		parent::__construct();
		parent::set_transient_for($mainObject->widgets['mainWindow']);
		parent::set_size_request(400, 280);

		parent::add_button(_t("Cancel"), GtkResponseType::CANCEL);
		parent::add_button(_t("Save"), GtkResponseType::OK);

		// Create the box
		$vbox = new GtkVBox();

		// Create fields
		$label = new GtkLabel("Name"); $label->set_xalign("0");
		$vbox->pack_start($label, TRUE, TRUE, 10);
		$this->widgets['name'] = new GtkEntry();
		$vbox->pack_start($this->widgets['name'], TRUE, TRUE, 10);

		$label = new GtkLabel("Host"); $label->set_xalign("0");
		$vbox->pack_start($label, TRUE, TRUE, 10);
		$this->widgets['host'] = new GtkEntry();
		$vbox->pack_start($this->widgets['host'], TRUE, TRUE, 10);

		$label = new GtkLabel("Username"); $label->set_xalign("0");
		$vbox->pack_start($label, TRUE, TRUE, 10);
		$this->widgets['username'] = new GtkEntry();
		$vbox->pack_start($this->widgets['username'], TRUE, TRUE, 10);

		$label = new GtkLabel("Password"); $label->set_xalign("0");
		$vbox->pack_start($label, TRUE, TRUE, 10);
		$this->widgets['password'] = new GtkEntry();
		$vbox->pack_start($this->widgets['password'], TRUE, TRUE, 10);

		$label = new GtkLabel("Database"); $label->set_xalign("0");
		$vbox->pack_start($label, TRUE, TRUE, 10);
		$this->widgets['database'] = new GtkEntry();
		$vbox->pack_start($this->widgets['database'], TRUE, TRUE, 10);


		// Add vbox
		$area = parent::get_content_area();
		$area->add($vbox);

		// Show all
		$area->show_all();
	}
}