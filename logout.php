<?php

	session_start();
	
	require_once 'class.userdao.php';
	$user = new UserDao();
	
	$user->logout();
