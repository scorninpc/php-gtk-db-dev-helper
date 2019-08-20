<?php

// Set locale
date_default_timezone_set("America/Sao_Paulo");

// Seta o tipo do erro
error_reporting(E_ALL & ~ E_WARNING & ~ E_NOTICE & ~ E_STRICT);
ini_set("display_errors", "On");

// Define aspath to application directory
defined("APPLICATION_PATH") || define("APPLICATION_PATH", dirname(__FILE__));

// Define application environment
defined("APPLICATION_ENV") || define("APPLICATION_ENV", (getenv("APPLICATION_ENV") ? getenv("APPLICATION_ENV") : "production"));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, [
	APPLICATION_PATH . "/src",
	APPLICATION_PATH . "/library",
	APPLICATION_PATH . "/library/Fabula",
	get_include_path()
]));

// Cria a aplicação
require_once ("Fabula/Application/Bootstrap.php");
$application = new \Fabula\Application\Bootstrap(
	APPLICATION_PATH . "/resources/config.php"
);

// Inicial a aplicação
$application->run();