<?php

/*
 * @author Giuseppe Guarino, peppeguarino -at- gmail.com
 * $Id: login.php 23 2013-07-02 09:36:21Z bastiancon3rio $
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
		<center><img src="images/cacti_logo.png" /></center>
		<ul class="pageitem">
			<li class="bigfield"><input placeholder="Username" name="login_username" type="text" /></li>
			<li class="bigfield"><input placeholder="Password" name="login_password" type="password" /></li>
			<li class="button"><input name="login" type="submit" value="login" /></li>
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

print "</div>";
print get_footer('login_page');
?>
</body>
</html>