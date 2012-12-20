<?php

/*
 * @author Giuseppe Guarino, peppeguarino -at- gmail.com
 *
 */

include("./lib/cSession.class.php");
include("./lib/auth.php");
include("../include/config.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta content="yes" name="apple-mobile-web-app-capable" />
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
	<meta content="minimum-scale=1.0, width=device-width, maximum-scale=0.6667, user-scalable=no" name="viewport" />
	<link href="css/style.css" rel="stylesheet" media="screen" type="text/css" />
	<script src="javascript/functions.js" type="text/javascript"></script>
	<title>CactiPhone</title>
	<meta content="CactiPhone, cacti, iPhone, Android, smartphone" name="keywords" />
	<meta content="CactiPhone bring cacti on your smartphone" name="description" />
	<link rel="apple-touch-icon" href="images/iphone-icon.png" />
	<style> a:link, a:visited, a:active { text-decoration: none; } </style>
</head>

<body>

	<div id="topbar">    
		<div id="title">CactiPhone</div>
		<div id="rightbutton">
			<a href="./logout.php" class="noeffect">logout</a> </div>
	</div>
	<div id="content">
		<span class="graytitle">Tree List</span>
		<ul class="pageitem">
		<?php

		$connessione = mysql_connect($database_hostname,$database_username,$database_password);
		@mysql_select_db($database_default) or die( "Unable to select database");
		
		$sql_where = "";

		if ($cactiUser["policy_trees"] == "1") {
			$sql_where = "where user_auth_perms.user_id is null";
			}elseif ($cactiUser["policy_trees"] == "2") {
				$sql_where = "where user_auth_perms.user_id is not null";
			}

			$sql = "select graph_tree.id, graph_tree.name, user_auth_perms.user_id
				from graph_tree left join user_auth_perms on (graph_tree.id=user_auth_perms.item_id and user_auth_perms.type=2 and user_auth_perms.user_id=".$cactiUser['id'].")
				$sql_where
				order by graph_tree.name";
			#echo $sql;

			$result = mysql_query($sql, $connessione);
			if ($result != null){
				while($row = mysql_fetch_array($result, MYSQL_ASSOC))
				{
					echo "<li class=\"menu\">\n\t<a href=\"view_multipleGraphs.php?tree_id=".$row['id']."&tree_name=".$row['name']."\">\n\t<span class=\"name\">".$row['name']."</span>\n\t<span class=\"arrow\"></span></a></li>\n";
				}
			}
			?>
		</ul>
	</div>
	<div id="footer">
		<a href="mailto:peppeguarino@gmail.com">Powered by Giuseppe Guarino</a>
		<br />
		<a class="noeffect" href="http://iwebkit.net">Made with iWebKit.</a>
	</body>
</html>