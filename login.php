<?php
	
	header('content-type: text/json');
	//If we don't set the header, jQuery will not know whether it's dealing with a json object
	
	session_start();
		
	if(!isset($_POST['email']) || !isset($_POST['password']))
		exit;
	
	require_once 'class.userdao.php';
	$user = new UserDao();
	
	$response = array();
	$email = $_POST['email'];
	$password = $_POST['password'];
	
	$validLogin = $user->login($email, $password);
	
	
	if($validLogin == 1) {
		$response['status'] = 'success';
	}
	else {
		$response['status'] = 'error';
	}
		
	echo json_encode($response);	