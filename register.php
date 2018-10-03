<?php
	//header('content-type: text/json');
	//If we don't set the header, jQuery will not know whether it's dealing with a json object 
	
	session_start();
	
	require_once 'class.userdao.php';
	$userDao = new UserDao();
	
	// Check if our user does exist, and is not logged in
	if($userDao->isLoggedIn()!="")
	{
		// TODO - let the JSON object handle this
		$userDao->redirect('../../dashboard.html');
	}
		
	// Register user
	if(!isset($_POST['email']) || !isset($_POST['password']))
		exit;
	
	$email = $_POST['email'];
	$password = $_POST['password'];
	$userDao->register($email, $password);
	
	// Set session
	$_SESSION['registeruser'] = 	$userDao->lastID();
	
	// Send email with token
	$userDao->sendActivationCode($email);;
	
	
	//echo json_encode(array('exists' => $query->rowCount() > 0));
	
	// TODO - return user json object with extra info 
	echo "Email sent to: " . $email;
	
