<?php

/*
 * @author Giuseppe Guarino, peppeguarino -at- gmail.com
 *
 */

function getRootName($connessione,$tree_id, $search_key){

	if (strlen($search_key) < 3){
		#Se è una root:
		$sql = "select name from graph_tree where id = $tree_id";
	}
	else {
		#Se è una leaf
		$sql  = "SELECT title FROM graph_tree_items ";
		$sql .= " where graph_tree_id = $tree_id ";
		$sql .= " and order_key = '".str_pad($search_key, 90, "0")."'";
	}

	#echo $sql; 
	
	$result = mysql_query($sql, $connessione);
	$row = mysql_fetch_array($result);

	return $row[0];
}

function getPreviousRootName($connessione,$tree_id, $search_key){

	if (strlen($search_key) < 3){
		#Se è una root:
		$sql = "select name from graph_tree where id = $tree_id";
	}
	else {
		#Se è una leaf
		$sql  = "SELECT title FROM graph_tree_items ";
		$sql .= " where graph_tree_id = $tree_id ";
		$sql .= " and order_key = '".str_pad($search_key, 90, "0")."'";
	}

	#echo $sql; 
	
	$result = mysql_query($sql, $connessione);
	$row = mysql_fetch_array($result);

	return $row[0];
}

function getPrevKey($search_key){
	
	# null -> 001 -> 001002
	
	if (strlen($search_key) <= 3){
		$search_key = "";
	}
	else {
		$search_key = substr($search_key, 0, -3);
	}
	
	return $search_key;
}

function tree_tier($order_key, $chars_per_tier = CHARS_PER_TIER) {
	$root_test = str_pad('', $chars_per_tier, '0');

	if (preg_match("/^$root_test/", $order_key)) {
		$tier = 0;
	}else{
		$tier = ceil(strlen(preg_replace("/0+$/",'',$order_key)) / $chars_per_tier);
	}

	return $tier;
}

function get_request_var_post($name, $default = "") {
	if (isset($_POST[$name])) {
		if (isset($_GET[$name])) {
			unset($_GET[$name]);
			$_REQUEST[$name] = $_POST[$name];
		}

		return $_POST[$name];
	}else{
		return $default;
	}
}

function mobile_qstr($s,$magic_quotes=false)
{	
	$replaceQuote = "\\'"; 	/// string to use to replace quotes
	
	if (!$magic_quotes) {
	
		if ($replaceQuote[0] == '\\'){
			// only since php 4.0.5
			$s = str_replace(array('\\',"\0"),array('\\\\',"\\\0"),$s);
		}
		return  "'".str_replace("'",$replaceQuote,$s)."'";
	}
	
	// undo magic quotes for "
	$s = str_replace('\\"','"',$s);
	
	if ($replaceQuote == "\\'")  // ' already quoted, no need to change anything
		return "'$s'";
	else {// change \' to '' for sybase/mssql
		$s = str_replace('\\\\','\\',$s);
		return "'".str_replace("\\'",$replaceQuote,$s)."'";
	}
}

function sanitize_search_string($string) {
	static $drop_char_match =   array('^', '$', '<', '>', '`', '\'', '"', '|', ',', '?', '+', '[', ']', '{', '}', '#', ';', '!', '=', '*');
	static $drop_char_replace = array(' ', ' ', ' ', ' ',  '',   '', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ');

	/* Replace line endings by a space */
	$string = preg_replace('/[\n\r]/is', ' ', $string);

	/* HTML entities like &nbsp; */
	$string = preg_replace('/\b&[a-z]+;\b/', ' ', $string);

	/* Remove URL's */
	$string = preg_replace('/\b[a-z0-9]+:\/\/[a-z0-9\.\-]+(\/[a-z0-9\?\.%_\-\+=&\/]+)?/', ' ', $string);

	/* Filter out strange characters like ^, $, &, change "it's" to "its" */
	for($i = 0; $i < count($drop_char_match); $i++) {
		$string =  str_replace($drop_char_match[$i], $drop_char_replace[$i], $string);
	}

	return $string;
}

function mobile_db_fetch_row($query){
	global $database_hostname,$database_username,$database_password, $database_default;
	
	$connessione = mysql_connect($database_hostname,$database_username,$database_password);
	@mysql_select_db($database_default) or die( "Unable to select database");

	$result = mysql_query($query, $connessione); # Get Auth Method
	$auth_method = mysql_fetch_assoc($result);
	return $auth_method;
}

function read_config_option($config_name, $force = FALSE) {
	global $config, $database_default;

	if (isset($_SESSION["sess_config_array"])) {
		$config_array = $_SESSION["sess_config_array"];
	}else if (isset($config["config_options_array"])) {
		$config_array = $config["config_options_array"];
	}

	if ((!isset($config_array[$config_name])) || ($force)) {
		$db_setting = mobile_db_fetch_row("select value from `$database_default`.`settings` where name='$config_name'", FALSE);

		if (isset($db_setting["value"])) {
			$config_array[$config_name] = $db_setting["value"];
		}else{
			$config_array[$config_name] = read_default_config_option($config_name);
		}

		if (isset($_SESSION)) {
			$_SESSION["sess_config_array"]  = $config_array;
		}else{
			$config["config_options_array"] = $config_array;
		}
	}

	return $config_array[$config_name];
}

function read_default_config_option($config_name) {
	global $config, $settings;

	if (is_array($settings)) {
		reset($settings);
		while (list($tab_name, $tab_array) = each($settings)) {
			if ((isset($tab_array[$config_name])) && (isset($tab_array[$config_name]["default"]))) {
				return $tab_array[$config_name]["default"];
			}else{
				while (list($field_name, $field_array) = each($tab_array)) {
					if ((isset($field_array["items"])) && (isset($field_array["items"][$config_name])) && (isset($field_array["items"][$config_name]["default"]))) {
						return $field_array["items"][$config_name]["default"];
					}
				}
			}
		}
	}
}

?>