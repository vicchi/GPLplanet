<?php
/**
 * Mysql connection/query class
 * @package gplplanet
 * @author Tyler Bell tylerwbell[at]gmail[dot]com
 * @copyright (C) 2009-2011 - Tyler Bell
 */



class db {
	const CONFIGFILE = "config.ini"; 	//script and db configuration
	public static $db = null;			//connection
	protected static $instance = null;  //db instance
	private function __clone(){}
	private function __construct() {
		//get config
		$thisDir = dirname(__FILE__);
		$configFile = $thisDir."/".self :: CONFIGFILE;		//config file assumed to be in same directory as this file
		if (is_readable($configFile)) {
			$cfg = parse_ini_file($configFile);
		} else {
			//can't operate without db configs, so barf and bail
			if (is_file($configFile)){
				$errMsg = "unreadable config file " . $configFile . "; check file permissions\n";
			} else {
				$errMsg = "missing config file " . $configFile . "\n";
			}
			throw new Exception(__METHOD__." ".$errMsg);
			return false;
		}
		//connect
		try {
            if (isset($cfg['socket']) && !empty($cfg['socket'])) {
                self::$db = new mysqli($cfg['host'], $cfg['username'], $cfg['password'],$cfg['database'], null, $cfg['socket']);
            }
            elseif (isset($cfg['port']) && !empty($cfg['port'])) {
                self::$db = new mysqli($cfg['host'], $cfg['username'], $cfg['password'],$cfg['database'], $cfg['port'], null);
            }
            else {
                self::$db = new mysqli($cfg['host'], $cfg['username'], $cfg['password'],$cfg['database'], null, null);
            }
		} catch (Exception $e) {
			$errMsg = "MySQL Connect to " . $cfg['host'] . "/" . $cfg['database'] . " failed: %s\n". mysqli_connect_error();
			throw new Exception(__METHOD__." ".$e->getMessage().": ".$errMsg);
			return false;
		}
		self::$db->set_charset("utf8"); //set client to utf8
		return true;
	}

	/**
	 * Singleton management
	 * @return db object
	 */
    public static function GetInstance(){
        if ( !(self::$instance instanceof db) ){
                self::$instance = new db();
        }
        return self::$instance;
	}

	/**
	 * Escapes string
	 * @param string str
	 * @return string
	 */
	public function escapeString($str){
		return self::$db->real_escape_string($str);
	}

	/**
	* Query database
	* @param string SQL SQL statement
	* @return mysql(i) result object
	*/
	public function query($SQL) {
		$result = self::$db->query($SQL);
		if (self::$db->error) {
			$errMsg = self::$db->error . " (" . $SQL . ")";
			throw new Exception(__METHOD__." ".$errMsg);
			return false;
		}
		return $result;
	}

	/**
	* Multi Query database
	* @param string SQL SQL statement
	* @return mysql(i) result object
	*/
	public function multiQuery($SQL) {
		if (self::$db->multi_query($SQL)) {
	    $i = 0;
		    do {
		        $i++;
		    } while (self::$db->next_result());
		}
		if (self::$db->errno) {
		    $errMsg = self::$db->error . " (" . $SQL . ")";
			throw new Exception(__METHOD__." ".$errMsg);
		    return false;
		} else {
			return true;
		}

		/*
		$result = self::$db->multi_query($SQL);
		if (self::$db->error) {
			$errMsg = self::$db->error . " (" . $SQL . ")";
			throw new Exception(__METHOD__." ".$errMsg);
			return false;
		}
		//iterate results to ensure that all queries are processed
		if ($result = $mysqli->use_result()) {

		} else {
			return false;
		}
		*/
	}
}

?>
