<?php

require ("../db/mySQLDAO.php");
//require ("../db/conn.php");

$config = parse_ini_file("../../../../mySQLApp.ini");

$returnValue = array();

//check for all required values
if(empty(filter_input(INPUT_GET,'userEmail')) || empty(filter_input(INPUT_GET,'userPassword')) || empty(filter_input(INPUT_GET,'userFirstName')) || empty(filter_input(INPUT_GET,'userLastName')))
{
    $returnValue["Status"]="400";
    $returnValue["Message"]="Missing required information";
    echo json_encode($returnValue);
    return;
    
} 
//convert input values to variables
$userEmail = htmlentities(filter_input(INPUT_GET,'userEmail'));
$userPassword = htmlentities(filter_input(INPUT_GET,'userPassword'));
$userFirstName = htmlentities(filter_input(INPUT_GET,'userFirstName'));
$userLastName = htmlentities(filter_input(INPUT_GET,'userLastName'));

//secure password
$salt = openssl_random_pseudo_bytes(16);
$secured_password = sha1($userPassword . $salt);


//get connection variables from ini data
$dbhost = trim($config["dbhost"]);
$dbuser = trim($config["dbuser"]);
$dbpassword = trim($config["dbpassword"]);
$dbname = trim($config["dbname"]);

//connect to db
$dao = new MySQLDAO($dbhost, $dbuser, $dbpassword, $dbname);

$dao->openConnection();

//check for existing user in database
$userDetails = $dao->getUserDetails($userEmail);
if(!empty($userDetails))
    {
    $returnValue["Status"]="400";
    $returnValue["Message"]="Email Address Exists";
    echo json_encode($returnValue);
    return;
}

//register new user in database
$result =$dao->registerUser($userEmail, $userFirstName, $userLastName, $secured_password, $salt);

if($result)
{
    $userDetails = $dao->getUserDetails($userEmail);
    $returnValue["status"]="200";
    $returnValue["message"]="Successfully registered new user";    
    $returnValue["userId"] = $userDetails["user_id"];
    $returnValue["userFirstName"] = $userDetails["first_name"];
    $returnValue["userLastName"] = $userDetails["last_name"];
    $returnValue["userEmail"] = $userDetails["email"]; 
} else {   
    $returnValue["status"]="400";
    $returnValue["message"]="Could not register user with provided information"; 
}
$dao->closeConnection();
echo json_encode($returnValue);
