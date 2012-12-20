<?php

/*
 * @author Giuseppe Guarino, peppeguarino -at- gmail.com
 *
 */

class cSession  {

	var $cactiUser = null; 

	function cSession(){
		if ( !$this->check()) {
		session_name('CactiPhone');
		session_start();
		$this->cactiUser = null; 
		}
	}
	
	function check() {
		$this->cactiUser = isset($_SESSION['user']) ? $_SESSION['user'] : null;
		
		if($this->cactiUser != null) {
			return true;
		}
		else {
			return false;
		}
	}

	function start($cactiUser){
		$_SESSION['name'] = session_name();
		$_SESSION['user'] = $cactiUser;
		header("location: ./index.php");
	}
	
	function destroy() {
		
		session_unset();
		session_destroy();
	}
	
	
	function getUser(){
		return $this->cactiUser;
	
	}
	
	function setTimeout($timeout){
		ini_set("session.gc_maxlifetime", $timeout);
	}
}

?>
