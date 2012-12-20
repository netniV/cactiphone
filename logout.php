<?php 

/*
 * @author Giuseppe Guarino, peppeguarino -at- gmail.com
 *
 */

include("./lib/cSession.class.php");
$session = new cSession();
	$session->destroy();
    header("Location: login.php");
?>

