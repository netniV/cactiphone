<?php

/*
 * @author Giuseppe Guarino, peppeguarino -at- gmail.com
 * $Id: auth.php 19 2013-06-12 13:46:51Z bastiancon3rio $
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