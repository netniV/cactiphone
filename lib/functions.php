<?php

/*
 * @author Giuseppe Guarino, peppeguarino -at- gmail.com
 * $Id: functions.php 25 2014-05-07 13:25:01Z bastiancon3rio $
 */

define("CHARS_PER_TIER", 3);

function getRootTree($cactiUser) {
	$sql_where = "";
	$toReturn = "";
	
	if ($cactiUser["policy_trees"] == "1") {
		$sql_where = "where user_auth_perms.user_id is null";
	}elseif ($cactiUser["policy_trees"] == "2") {
		$sql_where = "where user_auth_perms.user_id is not null";
	}

	$sql = "select graph_tree.id, graph_tree.name, user_auth_perms.user_id
			from graph_tree left join user_auth_perms on (graph_tree.id=user_auth_perms.item_id and user_auth_perms.type=2 and user_auth_perms.user_id=$cactiUser[id])
			$sql_where
			order by graph_tree.name";
			
	#echo $sql;
		
	$result = mobile_db_fetch_rows($sql);
	
	$toReturn .= "<ul class=\"pageitem\">";
	if ($result != null){
		foreach ($result as $row){
			$toReturn .= "<li class=\"menu\">\n\t";
			$toReturn .= "<a href=\"index.php?tree_id=$row[id]&tree_name=$row[name]\">\n\t";
			$toReturn .= "<span class=\"name\">$row[name]</span>\n\t";
			$toReturn .= "<span class=\"arrow\"></span>";
			$toReturn .= "</a>";
			$toReturn .= "</li>\n";
		}
	}
	$toReturn .= "</ul>";
	
	return $toReturn;
}

function getLeafs($cactiUser, $tree_id, $search_key, $tree_name, $host_id){
	
	$toReturn = "";
	$graphArray = $leafArray = $notLikeArray = array();
	
	$sql = "select graph_tree_items.id, graph_tree_items.title, graph_tree_items.local_graph_id, graph_tree_items.host_id, ".
		"graph_tree_items.position, graph_templates_graph.title_cache as graph_title, CONCAT_WS('',description,' (',hostname,')') as hostname ".
		"from graph_tree_items left join graph_templates_graph on ".
		"(graph_tree_items.local_graph_id=graph_templates_graph.local_graph_id and graph_tree_items.local_graph_id>0) ".
		"left join host on (host.id=graph_tree_items.host_id) ".
		"where graph_tree_items.graph_tree_id = $tree_id ".
		"and graph_tree_items.position like '$search_key%' ".
		"and position not like '".$search_key."000%' ".
		"order by graph_tree_items.position";
	
	//echo $sql;
	
	$result = mobile_db_fetch_rows($sql);
	
	array_push($leafArray, "<ul class=\"pageitem\">");
	foreach ($result as $row){
		if ($row["local_graph_id"] == 0) {

			$order_key = $row['position'];

			if ($row["title"] != "") {
				$gitem = "header";
				$title = $row['title'];

			} elseif ($row["host_id"] > 0) {
				$gitem = "host";
				$title = "Host: ".$row['hostname']; 
			}

			$tree_tier = substr($order_key, 0, (tree_tier($order_key) * CHARS_PER_TIER));

			if ($tree_tier != $search_key ){
				array_push($notLikeArray, $tree_tier);
			}

			$strLen = strlen($search_key) + CHARS_PER_TIER;
			//if (isset($raw['hostname'])){
	 		   if (! isset($_GET['hostname'])) {
			   //or ($_GET['hostname'] == $row['hostname'])) {
				if ($gitem == "host"){
					$toPush = "<li class=\"menu\"><a href=\"index.php?host_id=".$row['host_id']."&hostname=";
					$toPush .=  $row['hostname']."&tree_id=$tree_id&search_key=$tree_tier\">";
					$toPush .= "<span class=\"name\">$title</span><span class=\"arrow\"></span></a></li>";
				} else {
					$toPush = "<li class=\"menu\"><a href=\"index.php?tree_id=$tree_id&search_key=$tree_tier\">";
					$toPush .= "<span class=\"name\">$title</span><span class=\"arrow\"></span></a></li>";
				}
				array_push($leafArray, $toPush);	
			//}
			}
			//echo "<br>" . $row['hostname'] . $_GET['hostname'];
		}
	}
	array_push($leafArray, "</ul>");
	
	if (($host_id != null) && ($host_id != 0)){

		$sql = "SELECT graph_templates_graph.title_cache, graph_templates_graph.local_graph_id, graph_templates_graph.height ";
		$sql .=	"FROM ( graph_local, graph_templates_graph ) ";
		$sql .= "LEFT JOIN host ON (host.id=graph_local.host_id) ";
		$sql .=	"LEFT JOIN graph_templates ON (graph_templates.id=graph_local.graph_template_id) ";
		$sql .=	"LEFT JOIN user_auth_perms ON ((graph_templates_graph.local_graph_id=user_auth_perms.item_id ";
		$sql .=	"AND user_auth_perms.type=1 AND user_auth_perms.user_id=".$cactiUser['id'].") OR"; 		
		$sql .=	"(host.id=user_auth_perms.item_id and user_auth_perms.type=3 and user_auth_perms.user_id=".$cactiUser['id'];
		$sql .=	") OR (graph_templates.id=user_auth_perms.item_id AND ";
		$sql .=	"user_auth_perms.type=4 AND user_auth_perms.user_id=".$cactiUser['id'].")) ";

		$sql .=	"WHERE graph_local.id = graph_templates_graph.local_graph_id ";
		$sql .=	"AND graph_local.graph_template_id IN (	SELECT graph_templates.id FROM (graph_local,graph_templates,graph_templates_graph) ";
		$sql .=	"WHERE graph_local.id=graph_templates_graph.local_graph_id ";
		$sql .=	"AND graph_templates_graph.graph_template_id=graph_templates.id ";
		$sql .=	"AND graph_local.host_id=$host_id ";
		$sql .=	"GROUP BY graph_templates.id) ";
		$sql .=	"AND graph_local.host_id =$host_id ";
		$sql .=	"ORDER BY graph_templates_graph.title_cache";
		#echo $sql;

	} else {
		$sql = "select
			graph_tree_items.local_graph_id,
			graph_tree_items.position,
			graph_templates_graph.title_cache as graph_title,
			CONCAT_WS('',host.description,' (',host.hostname,')') as hostname,
			settings_tree.status
			from graph_tree_items
			left join graph_templates_graph on (graph_tree_items.local_graph_id=graph_templates_graph.local_graph_id and graph_tree_items.local_graph_id>0)
			left join settings_tree on (graph_tree_items.id=settings_tree.graph_tree_item_id and settings_tree.user_id=".$cactiUser['id'].")
			left join host on (graph_tree_items.host_id=host.id)
			left join graph_local on (graph_templates_graph.local_graph_id=graph_local.id)
			left join graph_templates on (graph_templates.id=graph_local.graph_template_id)
			left join user_auth_perms on ((graph_templates_graph.local_graph_id=user_auth_perms.item_id and user_auth_perms.type=1 and user_auth_perms.user_id=".$cactiUser['id'].") OR (host.id=user_auth_perms.item_id and user_auth_perms.type=3 and user_auth_perms.user_id=".$cactiUser['id'].") OR (graph_templates.id=user_auth_perms.item_id and user_auth_perms.type=4 and user_auth_perms.user_id=".$cactiUser['id']."))
			where graph_tree_items.graph_tree_id=$tree_id
			and graph_tree_items.position like '$search_key%'";

		if (sizeof($notLikeArray) != 0){
			foreach ($notLikeArray as $key){

				$sql .=	"and graph_tree_items.position NOT like '$key%'";

			}
		}

		$sql .= "and graph_tree_items.local_graph_id != 0 
			order by graph_tree_items.position";
	}
	#echo $sql;
	$result = mobile_db_fetch_rows($sql);
	foreach ($result as $row){
		$toPush = "<ul class=\"pageitem\">";
		$toPush .= "<li class=\"textbox\">";	
		$toPush .= "<a href=\"view_iGraph.php?graph_id=".$row['local_graph_id']."&tree_id=$tree_id&search_key=$search_key&prev_name=$tree_name\" alt=\"_blank\" >";
		$toPush .= "<img src=\"lib/make_image.php?local_graph_id=".$row['local_graph_id']."&rra_id=0\" />";
		$toPush .= "<span class=\"arrow\"></span></a>";
		$toPush .= "</li>";
		$toPush .= "</ul>";
		array_push($graphArray, $toPush);
	}
	
	if (sizeof($leafArray)!= 2){
		foreach ($leafArray as $leaf){ 
			$toReturn .= $leaf; 
		}
	}

	foreach ($graphArray as $myGraph){
		$toReturn .= $myGraph;
	}
	
	$toReturn .= "</div>";
	
	return $toReturn;
	
}

function getThold() {
	
	$thold_query = 'SELECT DISTINCT thold_data.*, data_template_data.name_cache, data_template_rrd.data_source_name
		FROM thold_data
		LEFT JOIN data_template_rrd ON data_template_rrd.id = thold_data.data_id
		LEFT JOIN data_template_data ON data_template_data.local_data_id = thold_data.rra_id 
		WHERE thold_alert > 0 AND thold_enabled = "on"
		ORDER BY name_cache ASC';
	
	
	$result = mobile_db_fetch_rows($thold_query);

	$toReturn = "<ul class=\"pageitem\">";
		if(sizeof($result) > 0){
			foreach ($result as $row){
				$toReturn .= "<li class=\"textbox\"><span class=\"header\">$row[name_cache]</span><p>$row[data_source_name] Current: $row[lastread]</p></li>";
			}
		} else {
			$toReturn .= "<li class=\"textbox\"><b align=\"center\">Everything is ok</b></li>";

		}
		$toReturn .= "</ul>";

		return $toReturn;
}

function getMonitor() {

	$monitor_query = 'SELECT description, status, status_fail_date, status_rec_date, status_last_error FROM host WHERE disabled = "" AND status < 3 ORDER BY status DESC';
	$result = mobile_db_fetch_rows($monitor_query);

	$toReturn = "<ul class=\"pageitem\">";
	if(sizeof($result) > 0){
		foreach ($result as $row){
			if($row['status'] == 2){
				$toReturn .= "<li class=\"textbox\"><img alt=\"up\" src=\"images/monitor/recover.png\" /><b> $row[description]</b></li>";
			} else if ($row['status'] == 1){
				$toReturn .= "<li class=\"textbox\"><img alt=\"up\" src=\"images/monitor/down.png\" /><b> $row[description]</b></li>";
			} else {}	
		}
	} else {
		$toReturn .= "<li class=\"textbox\"><b align=\"center\">All hosts are up&running</b></li>";

	}
	$toReturn .= "</ul>";
	
	return $toReturn;
}

function formatRequest($req) {
	$freq = Array(
		'search_key' => null,
		'name' => null,
		'prev_name' => null,
		'tree_id' => null,
		'hostname' => null,
		'host_id' => null,
		'graph_id' => null,
		'monitor' => null
	);
	
	foreach ($req as $key => $val){
		$freq[$key] = $val;
	}
	
	return $freq;
}

function getRootName($tree_id, $search_key){

	if (strlen($search_key) < 3){
		#Se è una root:
		$sql = "select name as index_name from graph_tree where id = $tree_id";
	}
	else {
		#Se è una leaf
		$sql  = "SELECT title AS index_name FROM graph_tree_items ";
		$sql .= " where graph_tree_id = $tree_id ";
		$sql .= " and position = '".str_pad($search_key, 90, "0")."'";
	}

	#echo $sql; 
	
	$rootName = mobile_db_fetch_row($sql);

	return $rootName['index_name'];
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

function mobile_db_fetch_row($sql){
	global $database_hostname,$database_username,$database_password, $database_default;
	
	$connessione = mysql_connect($database_hostname,$database_username,$database_password);
	@mysql_select_db($database_default) or die( "Unable to select database");

	$query = mysql_query($sql, $connessione);
	$result = null;
	if (isset($query) && ($query != null)){
		$result = mysql_fetch_assoc($query);
	}
	return $result;
}

function mobile_db_fetch_rows($sql){
	global $database_hostname,$database_username,$database_password, $database_default;
	
	$connessione = mysql_connect($database_hostname,$database_username,$database_password);
	@mysql_select_db($database_default) or die( "Unable to select database");

	$query = mysql_query($sql, $connessione);
	$resArray = array();
	if (isset($query) && ($query != null)){
		while($row = mysql_fetch_array($query, MYSQL_ASSOC)){
			array_push($resArray, $row);
		}
	}
	return $resArray;
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

function mobile_qstr($s,$magic_quotes=false) {	
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

function webBasicAuth(){
	if (isset($_SERVER["PHP_AUTH_USER"])) {
		$username = str_replace("\\", "\\\\", $_SERVER["PHP_AUTH_USER"]);
	}elseif (isset($_SERVER["REMOTE_USER"])) {
		$username = str_replace("\\", "\\\\", $_SERVER["REMOTE_USER"]);
	}elseif (isset($_SERVER["REDIRECT_REMOTE_USER"])) {
		$username = str_replace("\\", "\\\\", $_SERVER["REDIRECT_REMOTE_USER"]);
	}elseif (isset($_SERVER["HTTP_PHP_AUTH_USER"])) {
		$username = str_replace("\\", "\\\\", $_SERVER["HTTP_PHP_AUTH_USER"]);
	}elseif (isset($_SERVER["HTTP_REMOTE_USER"])) {
		$username = str_replace("\\", "\\\\", $_SERVER["HTTP_REMOTE_USER"]);
	}elseif (isset($_SERVER["HTTP_REDIRECT_REMOTE_USER"])) {
		$username = str_replace("\\", "\\\\", $_SERVER["HTTP_REDIRECT_REMOTE_USER"]);
	}else{ # ERROREEE
		die("Web Basic Authentication configured, but no username was passed from the web server.  Please make sure you have authentication enabled on the web server.");
	}
	$username = sanitize_search_string($username);
	$user = mobile_db_fetch_row("SELECT * FROM user_auth WHERE username = " . mobile_qstr($username) . " AND realm = 2");
	return $user;
}

function ldapAuth(){
	/* LDAP Auth */
	$username = sanitize_search_string(get_request_var_post("login_username"));

	/* include LDAP lib */
	include_once("./lib/ldap.php");
	$ldap_error = false;
	/* get user DN */
	$ldap_dn_search_response = cacti_ldap_search_dn($username);
	if ($ldap_dn_search_response["error_num"] == "0") {
		$ldap_dn = $ldap_dn_search_response["dn"];
		}else{
			/* Error searching */
			$ldap_error = true;
			$user = array( 'error' =>  "LDAP Search Error: " . $ldap_dn_search_response["error_text"]);
		}

		if (!$ldap_error) {
			/* auth user with LDAP */
			$ldap_auth_response = cacti_ldap_auth($username,stripslashes(get_request_var_post("login_password")),$ldap_dn);

			if ($ldap_auth_response["error_num"] == "0") {
				/* User ok */
				$user_auth = true;
				$copy_user = true;
				$realm = 1;
				/* Locate user in database */
				$user = mobile_db_fetch_row("SELECT * FROM user_auth WHERE username = " . mobile_qstr($username) . " AND realm = 1");
			}else{
				/* error */
				$user = array( 'error' => "LDAP Error: " . $ldap_auth_response["error_text"] );
			}
	}
		
	return $user;
}

function builtinAuth(){
	$pass_hash = mobile_db_fetch_row("SELECT password FROM user_auth WHERE username = " . mobile_qstr(get_request_var_post("login_username")) . " AND realm = 0")[password];
        $pass_plain = get_request_var_post("login_password"); 
	if (password_verify($pass_plain,$pass_hash)){
	   $user = mobile_db_fetch_row("SELECT * FROM user_auth WHERE username = " . mobile_qstr(get_request_var_post("login_username")) . " AND realm = 0");
           //$user = mobile_db_fetch_row("SELECT * FROM user_auth WHERE username = " . mobile_qstr(get_request_var_post("login_username")) . " AND password = '" . md5(get_request_var_post("login_password")) . "' AND realm = 0");
        }
	if (sizeof($user) < 2){
		$user = array( 'error' => "Invalid User Name/Password Please Retype");
		//$user = array( 'error' => "error $pass_hash $pass_plain $user");
	}
	
	return $user;
}

function get_footer($login = false ) {
        if($login != false ){
                $foot = '<div id="footer">';
        }else {
                $foot = '<div id="footer"><a href="./logout.php"><b>Logout</b></a>';
        }
		$foot .= '<a class="noeffect" href="https://sourceforge.net/projects/cactiphone/">CactiPhone Project</a><br />';
        $foot .= '<br/><br/><a href="mailto:peppeguarino@gmail.com">Powered by Giuseppe Guarino</a><br />';
        $foot .= '<a class="noeffect" href="http://iwebkit.net">Made with iWebKit</a><br />';
		$foot .= '</div>';

        return $foot;
}

?>
