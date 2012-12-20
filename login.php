<?php
/*
 * @author Giuseppe Guarino, peppeguarino -at- gmail.com
 * TODO: log into db..
 */

include("../include/config.php");
include("./lib/functions.php");

$auth_method = mobile_db_fetch_row("SELECT value FROM settings WHERE name = 'auth_method'");

switch($auth_method['value']){
	case 2:
		$user = webBasicAuth();
		break;
	case 3:
		if (get_request_var_post("login") != null){
			$user = ldapAuth();
		}
		break;
	default: 
		if (get_request_var_post("login") != null){
			$user = builtinAuth();
		}
		break;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta content="yes" name="apple-mobile-web-app-capable" />
<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
<meta content="minimum-scale=1.0, width=device-width, maximum-scale=0.6667, user-scalable=no" name="viewport" />
<link href="css/style.css" rel="stylesheet" media="screen" type="text/css" />
<script src="javascript/functions.js" type="text/javascript"></script>
<title>CactiPhone Login Page</title>
<meta content="CactiPhone, cacti, iPhone, Android, smartphone" name="keywords" />
<meta content="CactiPhone bring cacti on your smartphone" name="description" />
<link rel="apple-touch-icon" href="images/iphone-icon.png" />
</head>

<body>

<div id="topbar">
	<div id="title">CactiPhone Login Page</div>
</div>
<div id="content" action="login.php">
	<form method="post">
		<center><img src="../images/cacti_logo.gif" /></center>
		<ul class="pageitem">
			<li class="bigfield"><input placeholder="Username" name="login_username" type="text" /></li>
			<li class="bigfield"><input placeholder="Password" name="login_password" type="password" /></li>
			<li class="button"><input name="login" type="submit" value="Login" /></li>
		</ul>
		<fieldset></form>
<?php

/* Process the user  */
if (isset($user) && (sizeof($user) > 0)) {
	if (!isset($user['error'])){
		$user_enabled = $user["enabled"];
		if ($user_enabled != "on") {
			/* Display error */
			print "<div align='center' class='error'>Access Denied, user account disabled.</div>";
		} else {
			include("./lib/cSession.class.php");
			$session = new cSession();
			$session->start($user);
			$session->setTimeout(1600000);
		}

	} else {
		print "</div><div align='center' class='error'>".$user['error']."</div>";
	}
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
	$user = mobile_db_fetch_row("SELECT * FROM user_auth WHERE username = " . mobile_qstr(get_request_var_post("login_username")) . " AND password = '" . md5(get_request_var_post("login_password")) . "' AND realm = 0");

	if (sizeof($user) < 2){
		$user = array( 'error' => "Invalid User Name/Password Please Retype");
	}
	
	return $user;
}

?>
</div>
<div id="footer">
	<a href="mailto:peppeguarino@gmail.com">Powered by Giuseppe Guarino</a>
	<br />
	<a class="noeffect" href="http://iwebkit.net">Made with iWebKit.</a>
	
</div>
</body>
</html>