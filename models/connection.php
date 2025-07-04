<?php

class Connection{

	public static function connect(){
		try {
			$link = new PDO(
				"mysql:host=localhost;dbname=u735263260_posystem",
				"u735263260_jkduran1998",
				"Vupodan!97"
			);
			$link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$link->exec("set names utf8");
			return $link;
		} catch(PDOException $e) {
			error_log("Database connection error: " . $e->getMessage());
			throw $e;
		}
	}

}