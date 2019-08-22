<?php

namespace dbHelper\Helpers;

/**
 *
 */
class Database
{
	protected $host;
	protected $database;
	protected $username;
	protected $password;

	protected $connection;

	/**
	 *
	 */
	public function __construct($host, $database, $username, $password)
	{
		$this->host = $host;
		$this->database = $database;
		$this->username = $username;
		$this->password = $password;

		$dsn = "pgsql:host=$host;port=5432;dbname=$database;user=$username;password=$password";

		try {
			$this->connection = new \PDO($dsn);
			if(!$this->connection) {
				throw new \Exception("Cloud not connect to server");
			}
		}
		catch(\Exception $e) {
			throw $e;
		}
	}

	/**
	 *
	 */
	public function getDatabases()
	{
		$query = "SELECT datname FROM pg_database WHERE datistemplate = false;";
		$result = $this->connection->query($query);

		$databases = [];
		foreach ($result as $row) {
			$databases[] = $row['datname'];
		}

		sort($databases);

		return $databases;
	}

	/**
	 *
	 */
	public function getSchemas($database)
	{
		$exclude = ["pg_toast", "pg_temp_1", "pg_toast_temp_1", "information_schema", "pg_catalog"];

		$query = "select schema_name from information_schema.schemata WHERE schema_name NOT IN ('" . implode("','", $exclude) . "');";
		$result = $this->connection->query($query);

		$schemas = [];
		foreach ($result as $row) {
			$schemas[] = $row['schema_name'];
		}

		sort($schemas);

		return $schemas;
	}
}