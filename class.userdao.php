<?php

	require_once 'class.database.php';

	class UserDao
	{ 
		private $connection;
		 
		public function __construct() {
			
			$database = new Database();
			$this->connection = $database->getConnection();
		}
	 
		public function runQuery($sql) {
			$query = $this->connection->prepare($sql);
			return $query;
		}
		 
		public function lastID() {
		  $query = $this->connection->lastInsertId();
		  return $query;
		}
		 
		public function register($email, $password) {
			
			try {
				
				$hash = password_hash($password, PASSWORD_BCRYPT, array("cost" => 11));
				
				$this->connection->beginTransaction();
				
				// Insert into User
				$query = $this->connection->prepare("INSERT INTO user (email, password) VALUES(:email, :password)");
				$query->bindparam(":email", $email);
				$query->bindparam(":password", $hash);
				$query->execute();	
					
				// Create a token
				$lastID = $this->lastID();
				$query = $this->connection->prepare("INSERT INTO token (userID, pincode) VALUES(:userID, :pincode)");
				$query->bindparam(":userID", $lastID);
				$query->bindparam(":pincode", $this->generatePin());
				$query->execute(); 
				
				$this->connection->commit();
					
				return $query;
			}
			catch(PDOException $ex) {
				$this->connection->rollBack();
				echo $ex->getMessage();
			}	
		}
		
		
		private function generatePin() {
			$pin =  strval(rand(0, 9)) . '' .  strval(rand(0, 9)) . '' . strval(rand(0, 9)) . '' . strval(rand(0, 9));		
			return $pin;
		}
	 	 
		private function generateToken() {				
			$token = sha1(uniqid(rand(), true)); // returns a 64 charactar string		
			return $token;
		}
	 
		public function login($email, $password) {
			
			try {	
			
				$stmt = $this->connection->prepare("SELECT * FROM user WHERE email=:email");
				$stmt->execute(array(":email"=>$email));
				$row=$stmt->fetch(PDO::FETCH_ASSOC);
			   
				if($stmt->rowCount() == 1) {
					
					if($row['active']== 1) {
						
						if(password_verify($password, $row['password'])) {
							
							$_SESSION['user'] = $row['ID'];
							$_SESSION['name'] = $row['fullname'];
							$_SESSION['avatar'] = $row['avatar'];
							$_SESSION['starttime'] = time();
							
							return true;
						}
						else {
							//header("Location: index.php?error");
							//exit;
							return false;
						}
					}
					else {
						//header("Location: index.php?inactive");
						//exit;
						return false;
					} 
				}
				else {
					//header("Location: index.php?error");
					//exit;
					return false;
				}  
			}
			catch(PDOException $ex) {
				echo $ex->getMessage();
			}
		}
		
		
		public function loginWithUsername($username, $password) {
			
			try {	  
				
				$stmt = $this->connection->prepare("SELECT * FROM user WHERE fullname=:username");
				$stmt->execute(array(":username"=>$username));
				$row=$stmt->fetch(PDO::FETCH_ASSOC);
			   
				if($stmt->rowCount() == 1) {
					
					if($row['active']== 1) {
						
						if(password_verify($password, $row['password'])) {
							$_SESSION['user'] = $row['ID'];
							$_SESSION['starttime'] = time();
							//return true;
						}
						else {
							//header("Location: index.php?error");
							//exit;
							return false;
						}
					}
					else {
						//header("Location: index.php?inactive");
						//exit;
						return false;
					} 
				}
				else {
					// TODO - increment logonfailure
					return false;
				}  
			}
			catch(PDOException $ex) {
				echo $ex->getMessage();
			}
		}
		
		
		
		public function admLogin($username, $password) {
			
			try {
				
				$stmt = $this->connection->prepare("SELECT user.ID, user.fullname, user.email, user.password, user.avatar, user.active FROM user JOIN roles ON (user.ID = roles.userID) JOIN role ON (roles.roleID = role.ID) WHERE user.fullname=:username AND role.name = 'admin'");
				$stmt->execute(array(":username"=>$username));
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
			   
				if($stmt->rowCount() == 1) {
					
					if($row['active']== 1) {
						
						if(password_verify($password, $row['password'])) {
							
							//print_r($row);
							$_SESSION['user'] = $row['ID'];
							$_SESSION['name'] = $row['fullname'];
							$_SESSION['avatar'] = $row['avatar'];
							$_SESSION['starttime'] = time();
							
							return true;
						}
						else {
							//header("Location: index.php?error");
							//exit;
							return false;
						}
					}
					else {
						//header("Location: index.php?inactive");
						//exit;
						return false;
					} 
				}
				else {
					// TODO - increment logonfailure
					return false;
				}  
			}
			catch(PDOException $ex) {
				echo $ex->getMessage();
			}
		}
		
		
		
	 
		public function loginWithToken($email, $password, $token) {
			
		}
		
		public function validateToken($token, $email) { 
			
			$stmt = $this->connection->prepare("SELECT * FROM token WHERE email=:email AND active == 1");
			$stmt->execute(array(":email"=>$email));
			$userRow=$stmt->fetch(PDO::FETCH_ASSOC);
			
			if($stmt->rowCount() == 1) {
				
				if ($token == $userRow['pincode']) {
					$_SESSION['user'] = $userRow['ID'];
				}
				else {
					if ($userRow['attempts'] < 5) {
						//TODO - UPDATE attempts with one
					}	
					if ($userRow['attempts'] >= 5) {
						//TODO - disable token, or define a trigger in the db
					}
				}		
			}	
		}
		
		
		public function getUserID() {
			
		}
		
		public function getUser($userID) {
			
			try {	
			
				$stmt = $this->connection->prepare("SELECT fullname, email, active, avatar, phone, company, location, locale FROM user a LEFT JOIN userdetails b ON (a.ID = b.userID) WHERE a.ID=:id");
				$stmt->execute(array(":id"=>$userID));
				$result = $stmt->fetch(PDO::FETCH_ASSOC);
			   
				if($stmt->rowCount() == 1) {	
					//header('Content-type: application/json');
					echo json_encode($result);
				}
				else {
					//TODO - echo error
					return false;
				}	
			}
			catch(PDOException $ex) {
				echo $ex->getMessage();
			}
		}
		
		
		public function getPincode($email) {
			
			$pincode;
			
			try {				
				$stmt = $this->connection->prepare("SELECT pincode FROM token a JOIN user b ON a.userID = b.ID WHERE b.email=:email AND a.active = 1");
				$stmt->execute(array(":email"=>$email));
				$row=$stmt->fetch(PDO::FETCH_ASSOC);
				
				if($stmt->rowCount() == 1) {	
					return $row['pincode'];		
				}
				else {
					return false;
				}		
			}
			catch(PDOException $e) {
				$message = $e->getMessage();
				echo json_encode(array(message => $message));
			}
			catch (Exception $e) {
				$message = $e->getMessage();
				$code = $e->getCode();
				echo json_encode(array(message => $message, code => $code));
			}		
		}
		
		
		public function isLoggedIn()
		{
			if(isset($_SESSION['user']))
			{
				return true;
			}
		}
		
		
		public function isLoggedInAndExpired()
		{
			if(isset($_SESSION['user']) && time() - $_SESSION['starttime'] > 900)
			{
				return true;
			}	
		}
		
		
		public function logout()
		{
			session_destroy();
			$_SESSION['user'] = false;
		}
		
		
		public function suspend()
		{
			session_destroy();
			$_SESSION['user'] = false;
		}
		
		
		public function redirect($url)
		{
			header("Location: $url");
		}
		
		function sendMail($email, $message, $subject)
		{      
			require_once('mailer/class.phpmailer.php');
				
			$mail = new PHPMailer();
			$mail->IsSMTP(); 
			$mail->SMTPDebug  = 0;                     
			$mail->SMTPAuth   = false;                  
			$mail->SMTPSecure = "ssl";                 
			//$mail->Host       = "smtp.gmail.com";      
			$mail->Port       = 465;             
			$mail->AddAddress($email);
			//$mail->Username="yourgmailid@gmail.com";  
			//$mail->Password="yourgmailpassword";            
			$mail->SetFrom('noreply@geoip-db.com','Coding Cage');
			//$mail->AddReplyTo("you@yourdomain.com","Coding Cage");
			$mail->Subject    = $subject;
			$mail->MsgHTML($message);
			$mail->Send();
		} 
	
		function sendActivationCode($email)
		{     
			$pincode = $this->getPincode($email); // Must be retrieved before loading the template, pincode must be injected into mail body.
			
			//if (!$pincode){
				
				try {
					require '../../assets/php/vendor/phpmailer/PHPMailerAutoload.php';
					require_once '../../assets/php/email-template.php';
					
					$mail = new PHPMailer(true);
					$mail->CharSet = 'UTF-8';
					$mail->SMTPSecure = "ssl";
					$mail->Port       = 465; 					
					$mail->setFrom('noreply@geoip-db.com', '');
					$mail->addAddress($email);
					$mail->Subject = 'Please confirm your email address';
					$mail->msgHTML($emailbody);
					$mail->AltBody = 'Verify your email address.'; //Plain text alternative???
					$mail->send();
					throw new Exception('Thank you! A verification code has been sent to the email address you provided.', 0);
				} 
				catch (phpmailerException $e) {
					$message = $e->getMessage();
					$code = 1;
					echo json_encode(array(message => $message, code => $code));
				} 
				catch (Exception $e) {
					$message = $e->getMessage();
					$code = $e->getCode();
					echo json_encode(array(message => $message, code => $code));
				}
			//} 
		}

	}

