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
	<script type="text/JavaScript">
		var rtime = getURLParameter('rtime');
		if (rtime == null){
			rtime = 300;
		}
	
		timedRefresh(rtime, "Next refresh in <countdown> seconds");
	</script>
	<?php

	/*
	 * @author Giuseppe Guarino, peppeguarino -at- gmail.com
	 * $Id: monitor.php 19 2013-06-12 13:46:51Z bastiancon3rio $
	 */

	include("./lib/cSession.class.php");
	include("./lib/auth.php");
	include("../include/config.php");
	include("lib/functions.php");
	
	$ctime = 300;
	if (isset($_REQUEST['rtime'])){
		$ctime = $_REQUEST['rtime'];
	}
	
	?>
</head>

<body>
	<div id="topbar">    
		<div id="title">Monitor Hosts</div>
	</div>
	<div id="content">	
		<span class="graytitle">Refresh time</span>
		<ul class="pageitem">
			<form name="input" action="monitor.php" method="get">
			<li class="select"><select name="rtime"   onchange="this.form.submit();">
			<?php
				print "<option value=\"300\" id=\"countdown\">Next refresh in $ctime seconds</option>";
			?>
			<option value="10">10 Seconds</option>
			<option value="30">30 Seconds</option>
			<option value="60">60 Seconds</option>
			<option value="120">120 Seconds</option>
			<option value="300">300 Seconds</option>
			</select><span class="arrow"></span> </li>
			</form>
		</ul>
		<?php print getMonitor(); ?>
	</div>
	<?php print get_footer(); ?>
</body>
</html>