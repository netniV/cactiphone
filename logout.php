<?php 

/*
 * @author Giuseppe Guarino, peppeguarino -at- gmail.com
 * $Id: logout.php 19 2013-06-12 13:46:51Z bastiancon3rio $
 */

include("./lib/cSession.class.php");
$session = new cSession();
	$session->destroy();
    header("Location: login.php");
?>

