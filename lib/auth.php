<?php

/*
 * @author Giuseppe Guarino, peppeguarino -at- gmail.com
 *
 */

	$cactiUser = null;
	$SESSION = new cSession();


	function checkSession() {
		global $SESSION;
		global $cactiUser;

		if($SESSION->check()) {

		 $cactiUser = $SESSION->getUser();
		 return ;
		}
		else {
			header("location: ./login.php");
		}
	}


	checkSession();

?>