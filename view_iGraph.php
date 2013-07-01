<?php

/*
 * @author Giuseppe Guarino, peppeguarino -at- gmail.com
 * $Id: view_iGraph.php 19 2013-06-12 13:46:51Z bastiancon3rio $
 */

include("./lib/cSession.class.php");
include("./lib/auth.php");
include("./lib/functions.php");
include("../include/config.php");

$request = formatRequest($_REQUEST);

$tree_name = getRootName($request['tree_id'], $request['search_key']);

$hostid = false;
if ($tree_name == null){
	$tree_name = $request['prev_name'];
	$hostid = true;
}

$row = mobile_db_fetch_row("SELECT title_cache FROM graph_templates_graph WHERE local_graph_id = $request[graph_id]");
$nome_graph = $row['title_cache'];

?>
<head>
	<meta content="yes" name="apple-mobile-web-app-capable" />
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
	<meta content="minimum-scale=1.0, width=device-width, maximum-scale=0.6667, user-scalable=no" name="viewport" />
	<link href="css/style.css" rel="stylesheet" media="screen" type="text/css" />
	<script src="javascript/functions.js" type="text/javascript"></script>
	<script type="text/javascript" >timedRefresh(60, "Next refresh in <countdown> seconds");</script>
	<title><?php print $nome_graph;?></title>
	<meta content="CactiPhone, cacti, iPhone, Android, smartphone" name="keywords" />
	<meta content="CactiPhone bring cacti on your smartphone" name="description" />
	<link rel="apple-touch-icon" href="images/iphone-icon.png" />
</head>

<body>

	<div id="topbar">
		<div id="title"><?php print $nome_graph;?></div>
		<div id="rightbutton"><a href="./monitor.php" class="noeffect" target="_blank">Monitor</a></div>
		<div id="leftnav">
			<?php
			#http://localhost/cactiphone-code/trunk/cactiphone2/view_iGraph.php?graph_id=1049&tree_id=15&search_key=&prev_name=CVP
			if ($hostid){
				print "<a href=\"index.php\"><img alt=\"home\" src=\"images/home.png\" /></a><a href=\"index.php?host_id=$request[tree_id]&tree_id=$request[tree_id]&search_key=$request[search_key]&hostname=$tree_name\">$tree_name</a></div>";
				
			} 
			else {
				print "<a href=\"index.php\"><img alt=\"home\" src=\"images/home.png\" /></a><a href=\"index.php?tree_id=$request[tree_id]&search_key=$request[search_key]\">$tree_name</a></div>";
			}
				
			?>
	</div>
	<div id="content">
		<span class="graytitle" id="countdown">Next refresh in 60 seconds</span>
		<ul class="pageitem">
			<li class="textbox"><center><span class="header">Daily (5 Minute Average)</span></center><a href="lib/make_image.php?local_graph_id=<?php print $request['graph_id'];?>&rra_id=1" target="_blank"><img src="lib/make_image.php?local_graph_id=<?php print $request['graph_id'];?>&rra_id=1" /></a></li>
			</ul>
			<ul class="pageitem">
			<li class="textbox"><center><span class="header">Weekly (30 Minute Average)</span></center><a href="lib/make_image.php?local_graph_id=<?php print $request['graph_id'];?>&rra_id=2" target="_blank"><img src="lib/make_image.php?local_graph_id=<?php print $request['graph_id'];?>&rra_id=2" /></a></li>
			</ul>
			<ul class="pageitem">
			<li class="textbox"><center><span class="header">Monthly (2 Hour Average)</span></center><a href="lib/make_image.php?local_graph_id=<?php print $request['graph_id'];?>&rra_id=3" target="_blank"><img src="lib/make_image.php?local_graph_id=<?php print $request['graph_id'];?>&rra_id=3" /></a></li>
			</ul>
			<ul class="pageitem">
			<li class="textbox"><center><span class="header">Yearly (1 Day Average)</span></center><a href="lib/make_image.php?local_graph_id=<?php print $request['graph_id'];?>&rra_id=4" target="_blank"><img src="lib/make_image.php?local_graph_id=<?php print $request['graph_id'];?>&rra_id=4" /></a></li>
		</ul>
	</div>
	<?php get_footer(); ?>
	</body>
	</html>