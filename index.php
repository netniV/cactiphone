<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta content="yes" name="apple-mobile-web-app-capable" />
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
	<meta content="minimum-scale=1.0, width=device-width, maximum-scale=0.6667, user-scalable=no" name="viewport" />
	<link href="css/style.css" rel="stylesheet" media="screen" type="text/css" />
	<script src="javascript/functions.js" type="text/javascript" ></script>
	<script type="text/javascript" >timedRefresh(60, "Next refresh in <countdown> seconds");</script>
	<title>CactiPhone</title>
	<meta content="CactiPhone, cacti, iPhone, Android, smartphone" name="keywords" />
	<meta content="CactiPhone bring cacti on your smartphone" name="description" />
	<link rel="apple-touch-icon" href="images/iphone-icon.png" />
	<style> a:link, a:visited, a:active { text-decoration: none; } </style>
	<?php
		/* 
			@author Giuseppe Guarino, peppeguarino -at- gmail.com 
			$Id: index.php 19 2013-06-12 13:46:51Z bastiancon3rio $
		*/
		include("./lib/cSession.class.php");
		include("./lib/auth.php");
		include("../include/config.php");
		include("./lib/functions.php");
		
		$request = formatRequest($_REQUEST);
				
		$topbar = "<div id=\"rightbutton\"><a href=\"./monitor.php\" class=\"noeffect\" target=\"_blank\">Monitor</a></div>";
		
		if (isset($request['tree_id']) || isset($request['name'])){
			
			if (isset($request['hostname'])){
				$tree_name = $request['hostname'];
			} else {
				$tree_name = getRootName($request['tree_id'], $request['search_key']);
			}
			
			$topbar .= "<div id='title'>$tree_name</div>";
			$topbar .= '<div id="leftnav"><a href="index.php"><img alt="home" src="images/home.png" /></a>';
			
			if ($request['search_key'] != ""){
				$old_search_key = getPrevKey($request['search_key']);
				$prev_tree_name = getRootName($request['tree_id'], getPrevKey($request['search_key'])); # Previous Root Name
				$topbar .= "<a href=\"index.php?tree_id=$request[tree_id]&search_key=$old_search_key\">$prev_tree_name</a>";
			} 
			$topbar .= '</div>';
		} else {
			$topbar .= "<div id='title'>CactiPhone</div>";
		}
	?>
</head>
<body>
	<div id="topbar">   
		<?php print $topbar; ?>
	</div>
	<div id="content">
		<span class="graytitle" id="countdown">Next refresh in 60 seconds</span>
		<?php
			if (!isset($request['tree_id']) && !isset($request['name'])){
				print getRootTree($cactiUser);
			} else {
				print getLeafs($cactiUser, $request['tree_id'], $request['search_key'], $tree_name, $request['host_id']);
			}

			print get_footer();
		?>
</body>
</html>