<?php

class DatabaseHelper
{

	static function getAllDatabases($connection)
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

	static function getAllShemas($connection)
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

	static function getAllTables($connection, $schema)
	{
		$query = "SELECT * FROM information_schema.tables WHERE table_schema = '" . $schema . "'";

		$result = $connection->query($query);

		$tables = [];
		foreach ($result as $row) {
			$tables[] = $row['table_name'];
		}

		sort($tables);

		return $tables;
	}

}