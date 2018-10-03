<?php

	session_start();

	require_once 'class.userdao.php';
	$user = new UserDao();

	// Default behaviour
	if(!isset($_POST['state'])) {
		//exit;
		if ($user->isLoggedIn()) {		
			// This will echo a json object with fullname and avatar
			$user->getUser($_SESSION['user']);
		}
		else
			return false;
	}
	
	// TODO - Switch	
	else {
	
		$state = $_POST['state'];
		
		// Logged in an not expired
		if ($state == "Logged in") {
			if ($user->isLoggedIn()) {		
				// This will echo a json object with fullname and avatar
				$user->getUser($_SESSION['user']);
			}
			else
				return false;
		}
		
		// logged in and expired
		if ($state == "Is expired") {
			if ($user->isLoggedInAndExpired()) {		
				// This will echo a json object with fullname and avatar
				$user->getUser($_SESSION['user']);
			}
			else
				return false;
		}
		
		else
			return false;
	
	}