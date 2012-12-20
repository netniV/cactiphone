<?php

/*
 * @author Giuseppe Guarino, peppeguarino -at- gmail.com
 *
 */

include("./lib/cSession.class.php");
include("./lib/auth.php");
include("./lib/functions.php");
include("../include/config.php");


$id = $_REQUEST['graph_id'];
$tree_id = $_REQUEST['tree_id'];
$search_key = $_GET['search_key'];

$connessione = mysql_connect($database_hostname,$database_username,$database_password);
@mysql_select_db($database_default) or die( "Unable to select database");

$tree_name = getPreviousRootName($connessione, $tree_id, $search_key);

$query = "SELECT title_cache FROM graph_templates_graph WHERE local_graph_id = $id";

// esecuzione della query
$result = mysql_query($query, $connessione);
$row = mysql_fetch_assoc($result);
$nome_graph = $row['title_cache'];

?>
<head>
	<meta content="yes" name="apple-mobile-web-app-capable" />
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
	<meta content="minimum-scale=1.0, width=device-width, maximum-scale=0.6667, user-scalable=no" name="viewport" />
	<link href="css/style.css" rel="stylesheet" media="screen" type="text/css" />
	<script src="javascript/functions.js" type="text/javascript"></script>
	<title><?php echo $nome_graph;?></title>
	<meta content="CactiPhone, cacti, iPhone, Android, smartphone" name="keywords" />
	<meta content="CactiPhone bring cacti on your smartphone" name="description" />
	<link rel="apple-touch-icon" href="images/iphone-icon.png" />
</head>

<body>

	<div id="topbar">
		<div id="leftnav">
			<?php
			
			echo "<a href=\"index.php\"><img alt=\"home\" src=\"images/home.png\" /></a><a href=\"view_multipleGraphs.php?tree_id=$tree_id&search_key=$search_key\">$tree_name</a></div>";
				
			?>
	</div>
	<div id="content">
		<ul class="pageitem">
			<li class="textbox"><center><span class="header">Daily (5 Minute Average)</span></center><a href="lib/make_image.php?local_graph_id=<?php echo $id;?>&rra_id=1"><img src="lib/make_image.php?local_graph_id=<?php echo $id;?>&rra_id=1" /></a></li>
			<li class="textbox"><center><span class="header">Weekly (30 Minute Average)</span></center><a href="lib/make_image.php?local_graph_id=<?php echo $id;?>&rra_id=2"><img src="lib/make_image.php?local_graph_id=<?php echo $id;?>&rra_id=2" /></a></li>
			<li class="textbox"><center><span class="header">Monthly (2 Hour Average)</span></center><a href="lib/make_image.php?local_graph_id=<?php echo $id;?>&rra_id=3"><img src="lib/make_image.php?local_graph_id=<?php echo $id;?>&rra_id=3" /></a></li>
			<li class="textbox"><center><span class="header">Yearly (1 Day Average)</span></center><a href="lib/make_image.php?local_graph_id=<?php echo $id;?>&rra_id=4"><img src="lib/make_image.php?local_graph_id=<?php echo $id;?>&rra_id=4" /></a></li>
		</ul>
	</div>
	<div id="footer">
		<!-- Support iWebKit by sending us traffic; please keep this footer on your page, consider it a thank you for our work :-) -->
		<a href="mailto:peppeguarino@gmail.com">Powered by Giuseppe Guarino</a>
		<br />
		<a class="noeffect" href="http://iwebkit.net">Made with iWebKit.</a>

	</body>

	</html>