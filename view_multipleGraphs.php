<?php

/*
 * @author Giuseppe Guarino, peppeguarino -at- gmail.com
 *
 */

include("./lib/cSession.class.php");
include("./lib/auth.php");
include("../include/config.php");
include("lib/functions.php");

define("CHARS_PER_TIER", 3);

$graphArray = $leafArray = $notLikeArray = array();

$tree_id = $_GET['tree_id'];

if (isset($_GET['search_key'])){
	$search_key = $_GET['search_key'];
}
else {
	$search_key = null;
}

$connessione = mysql_connect($database_hostname,$database_username,$database_password);
@mysql_select_db($database_default) or die( "Unable to select database");

if (isset($_GET['hostname'])){
	$tree_name = $_GET['hostname'];
} else {
	$tree_name = getRootName($connessione, $tree_id, $search_key);
}
$prev_tree_name = getPreviousRootName($connessione, $tree_id, getPrevKey($search_key));

# Ricava l'alberatura MA NON I GRAFICI (solo rami) della ROOT
/*
$sql  = "SELECT * FROM  `graph_tree_items` where graph_tree_id = $tree_id and title != \"\" ";
$sql .= "and order_key like '$search_key%'";
$sql .= "and order_key not like '".$search_key."000%'";
$sql .= "order by order_key";
*/


$sql = "select graph_tree_items.id, graph_tree_items.title, graph_tree_items.local_graph_id, graph_tree_items.host_id, ";
$sql .= "graph_tree_items.order_key, graph_templates_graph.title_cache as graph_title, CONCAT_WS('',description,' (',hostname,')') as hostname ";
$sql .= "from graph_tree_items left join graph_templates_graph on (graph_tree_items.local_graph_id=graph_templates_graph.local_graph_id and graph_tree_items.local_graph_id>0) ";
$sql .= "left join host on (host.id=graph_tree_items.host_id) ";
$sql .= "where graph_tree_items.graph_tree_id = $tree_id ";
$sql .= "and graph_tree_items.order_key like '$search_key%' ";
$sql .= "and order_key not like '".$search_key."000%' ";
$sql .= "order by graph_tree_items.order_key";


$result = mysql_query($sql, $connessione);

array_push($leafArray, "<ul class=\"pageitem\">");

while($row = mysql_fetch_array($result, MYSQL_ASSOC))
{
	
	if ($row["local_graph_id"] == 0) {
		
		$order_key = $row['order_key'];
		
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

		if ($strLen == strlen($tree_tier)){
			if ($gitem == "host"){
				$toPush = "<li class=\"menu\"><a href=\"view_multipleGraphs.php?host_id=".$row['host_id']."&hostname=";
				$toPush .=  $row['hostname']."&tree_id=$tree_id&search_key=$tree_tier\">";
				$toPush .= "<span class=\"name\">$title</span><span class=\"arrow\"></span></a></li>";

			} else {
				$toPush = "<li class=\"menu\"><a href=\"view_multipleGraphs.php?tree_id=$tree_id&search_key=$tree_tier\">";
				$toPush .= "<span class=\"name\">$title</span><span class=\"arrow\"></span></a></li>";
			}

			array_push($leafArray, $toPush);	
		}
	}

}
array_push($leafArray, "</ul>");

if (isset($_GET['host_id']) && ($_GET['host_id'] != 0)){
	
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
	$sql .=	"AND graph_local.host_id=".$_GET['host_id']." ";
	$sql .=	"GROUP BY graph_templates.id) ";
	$sql .=	"AND graph_local.host_id =".$_GET['host_id']." ";
	$sql .=	"ORDER BY graph_templates_graph.title_cache";
	
} else {
	$sql = "select
		graph_tree_items.local_graph_id,
		graph_tree_items.order_key,
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
		and graph_tree_items.order_key like '$search_key%'";

	if (sizeof($notLikeArray) != 0){
		foreach ($notLikeArray as $key){

			$sql .=	"and graph_tree_items.order_key NOT like '$key%'";

		}
	}

	$sql .= "and graph_tree_items.local_graph_id != 0 
		order by graph_tree_items.order_key";
}



# DEBUG
#echo $sql;

$result = mysql_query($sql, $connessione);

# Stampa Grafici
while($row = mysql_fetch_array($result, MYSQL_ASSOC))
{

	$toPush = "<ul class=\"pageitem\">";
	$toPush .= "<li class=\"textbox\">";	
	$toPush .= "<a href=\"view_iGraph.php?graph_id=".$row['local_graph_id']."&tree_id=$tree_id&search_key=$search_key\" alt=\"_blank\" >";
	$toPush .= "<img src=\"lib/make_image.php?local_graph_id=".$row['local_graph_id']."&rra_id=0\" />";
	$toPush .= "<span class=\"arrow\"></span></a>";
	$toPush .= "</li>";
	$toPush .= "</ul>";
	array_push($graphArray, $toPush);
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
	<title>CactiPhone</title>
	<meta content="CactiPhone, cacti, iPhone, Android, smartphone" name="keywords" />
	<meta content="CactiPhone bring cacti on your smartphone" name="description" />
	<link rel="apple-touch-icon" href="images/iphone-icon.png" />
	<style> a:link, a:visited, a:active { text-decoration: none; } </style>
</head>

<body>
	<div id="topbar">    
		<div id="title"><?php echo $tree_name?></div>
		<div id="leftnav">
			<a href="index.php"><img alt="home" src="images/home.png" /></a>
			<?php

			$old_search_key = getPrevKey($search_key);
			
			if ($search_key != ""){
				echo "<a href=\"view_multipleGraphs.php?tree_id=$tree_id&search_key=".$old_search_key."\">$prev_tree_name</a>";
			}
			
		#echo "<a href=\"view_multipleGraphs.php?tree_id=$tree_id&tree_name=$tree_name\">$tree_name</a>";

		#http://10.31.68.55/cacti/iPhone/view_multipleGraphs.php?tree_id=6&tree_name=Radius%20PPU&search_key=009

		?>
	</div>
</div>
<div id="content">

	<?php


if (sizeof($leafArray)!= 2){
	foreach ($leafArray as $leaf){ 
		echo $leaf; 
	}
}

foreach ($graphArray as $myGraph){

	echo $myGraph;

}

?>

</div>
<div id="footer">
	<!-- Support iWebKit by sending us traffic; please keep this footer on your page, consider it a thank you for our work :-) -->
	<a href="mailto:peppeguarino@gmail.com">Powered by Giuseppe Guarino</a>
	<br />
	<a class="noeffect" href="http://iwebkit.net">Made with iWebKit.</a>

</body>
</html>
