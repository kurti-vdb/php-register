<?php
	
	class Database
	{
		protected $connection;
		
		private $dsn = 'mysql:dbname=hostname;host=ip-address';
		private $username = 'username';
		private $password = "password";
		
		public function __construct()
		{
			$this->connect();
		}
		
		/*
		public function __construct($dsn, $username, $password)
		{
			$this->dsn = $dsn;
			$this->username = $username;
			$this->password = $password;
			$this->connect();
		}
		*/
		
		private function connect()
		{
			$this->connection = new PDO($this->dsn, $this->username, $this->password);
			$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
		}
		
		public function getConnection()
		{
			 return $this->connection;
		}
		
		public function close()
		{		
			$this->connection = null;
		}
		
		public function __sleep()
		{
			return array('dsn', 'username', 'password');
		}
		
		public function __wakeup()
		{
			$this->connect();
		}	
	}
	
	
