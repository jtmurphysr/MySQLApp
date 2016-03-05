<?php

class mySQLDAO
{
    var $dbhost = null;
    var $dbuser = null;
    var $dbpass = null;
    var $conn = null;
    var $dbname = null;
    var $result = null;
    
    function __construct($dbhost, $dbuser, $dbpassword, $dbname){
        
        $this->dbhost = $dbhost;
        $this->dbuser = $dbuser;
        $this->dbpass = $dbpassword ;
        $this->dbname = $dbname;
        
    }
    
    public function openConnection() {
        $this->conn = new mysqli($this->dbhost, $this->dbuser, $this->dbpass, $this->dbname);
        if (mysqli_connect_errno()) {
            throw new Exception("Could not establish connection to database");
        }
        $this->conn->set_charset("utf8");
        
    }
    
    public function closeConnection() {
        if ($this->conn != null){
            $this->conn->close();
        }
    }
    
    public function getUserDetails($email) {
        $returnValue = array();
        $sql = "select * from users where email='" . $email . "'";
        
        $result = $this->conn->query($sql);
        if($result != null && (mysqli_num_rows($result) >= 1)){
            $row = $result->fetch_array(MYSQLI_ASSOC);
            if(!empty($row)){
                $returnValue = $row;
            }
        }
        return $returnValue;
    }
    
    
     public function registerUser($email, $first_name, $last_name, $password, $salt)
    { 
        $sql = "insert into users set email=?, first_name=?, last_name=?, user_password=?, salt=?";
        $statement = $this->conn->prepare($sql);
        if (!$statement){
            throw new Exception($statement->error);
        }
        $statement->bind_param("sssss", $email, $first_name, $last_name, $password, $salt);
        $returnValue = $statement->execute();
        return $returnValue;  
    }
}
