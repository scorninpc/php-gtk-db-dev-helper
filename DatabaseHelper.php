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

		return $databases;
	}

	static function getAllShemas($connection)
	{
		$query = "select schema_name from information_schema.schemata;";
		$result = $connection->query($query);

		$schemas = [];
		foreach ($result as $row) {
			$schemas[] = $row['schema_name'];
		}

		return $schemas;
	}

}