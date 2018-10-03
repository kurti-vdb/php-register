<?php
	header('content-type: text/json');
	//If we don't set the header, jQuery will not know whether it's dealing with a json object 
	
	if(!isset($_POST['email']))
		exit;

	require_once 'class.database.php';

	try {
		$database = new Database();
		$connection = $database->getConnection();
	} 
	catch (PDOException $e) {
		echo 'Connection failed: ' . $e->getMessage();
	}
	
	$query = $connection->prepare('SELECT * FROM user WHERE email = :email');
	$query->bindParam(':email', $_POST['email']);
	$query->execute();

	echo json_encode(array('exists' => $query->rowCount() > 0));
	
