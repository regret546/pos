<?php

class Connection{

	public static function connect(){
		try {
			$link = new PDO("mysql:host=localhost;dbname=posystem", "root", "");
			$link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$link->exec("set names utf8");
			return $link;
		} catch(PDOException $e) {
			error_log("Database connection error: " . $e->getMessage());
			throw $e;
		}
	}

}