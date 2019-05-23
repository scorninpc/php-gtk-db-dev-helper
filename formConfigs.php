<?php

/**
 * Form with configs info
 */
class formConfigs extends GtkDialog
{
	public function __construct($mainObject) 
	{
		parent::__construct();
		parent::set_title(_t("Settings"));
		parent::set_transient_for($mainObject->widgets['mainWindow']);
		parent::set_size_request(400, 280);

		parent::add_button(_t("Cancel"), GtkResponseType::CANCEL);
		parent::add_button(_t("Save"), GtkResponseType::OK);

		// Create the box
		$vbox = new GtkVBox();


		// Create fields
		$label = new GtkLabel(_t("Server Config Path")); $label->set_xalign("0");
		$vbox->pack_start($label, TRUE, TRUE, 10);

		$hbox = new GtkHBox();
		$vbox->pack_start($hbox, TRUE, TRUE, 10);

		$this->widgets['servers_config_path'] = new GtkEntry();
		$hbox->pack_start($this->widgets['servers_config_path'], TRUE, TRUE, 10);
		$button = GtkButton::new_with_label("...");

		$button->connect('clicked', function($widget, $mainObject) {
				
			// Create the file save dialog
			$dialog = new GtkFileChooserDialog(_t("Save File"), $mainObject->widgets['mainWindow'], GtkFileChooserAction::SAVE, [
				"Cancel", GtkResponseType::CANCEL,
				"Ok", GtkResponseType::OK,
			]);

			// Add the filter
			$filter = new GtkFileFilter();
			$filter->set_name("JSON Files");
			$filter->add_pattern("*.json");
			$dialog->add_filter($filter);

			// Open the file save dialog
			$a = $dialog->run();
			if($a == GtkResponseType::OK) {
				$filename = $dialog->get_filename();
				
				if(substr($filename, strrpos($filename, ".json")) != ".json") {
					$filename .= ".json";
				}

				$this->widgets['servers_config_path']->set_text($filename);
			}
			$dialog->destroy();

		}, $mainObject);
		$hbox->pack_start($button, FALSE, FALSE, 10);



		// Add vbox
		$area = parent::get_content_area();
		$area->add($vbox);

		// Show all
		$area->show_all();
	}
}