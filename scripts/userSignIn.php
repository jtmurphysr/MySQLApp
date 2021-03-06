<?php

require ("../db/mySQLDAO.php");

$config = parse_ini_file("../../../../mySQLApp.ini");
$returnValue = array();

//decod json input to array
$input = json_decode(file_get_contents('php://input'),true);

//convert input values to variables
$userEmail = htmlentities($input["userEmail"]);
$userPassword = htmlentities($input["userPassword"]);

//check for all required values
if(($userEmail == null) || ($userPassword == null))
{
    $returnValue["Status"]="400";
    $returnValue["Message"]="Missing required information";
    echo json_encode($returnValue);
    return;
    
} 



//get connection variables from ini data
$dbhost = trim($config["dbhost"]);
$dbuser = trim($config["dbuser"]);
$dbpassword = trim($config["dbpassword"]);
$dbname = trim($config["dbname"]);

//connect to db
$dao = new MySQLDAO($dbhost, $dbuser, $dbpassword, $dbname);

$dao->openConnection();
//get user details from database
$userDetails = $dao->getUserDetails($userEmail);

if(empty($userDetails))
    {
    $returnValue["Status"]="400";
    $returnValue["Message"]="User Not Found";
    echo json_encode($returnValue);
    return;
}

$userSecuredPassword = $userDetails["user_password"];
$userSalt = $userDetails["salt"];

//check password, if match return data, else return error
if($userSecuredPassword === sha1($userPassword . $userSalt))
{
    $returnValue["status"]="200";
    $returnValue["userFirstName"] = $userDetails["first_name"];
    $returnValue["userLastName"] = $userDetails["last_name"];
    $returnValue["userEmail"] = $userDetails["email"];
    $returnValue["userId"] = $userDetails["user_id"];
} else {
    $returnValue["status"]="403";
    $returnValue["message"]="User not found";
    echo json_encode($returnValue);
    return;
}
$dao->closeConnection();
echo json_encode($returnValue);