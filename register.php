<?php
require_once 'db.php';

$username = $_POST['username'];
$email =$_POST['email'];
$password=password_hash($_POST['password'],PASSWORD_BCRYPT);

$stmt=$conn->prepare(
    "INSERT INTO users (username,email,password)VALUES(?,?,?)"
);
$stmt->bind_param("sss",$username,$email,$password);
if($stmt->execute()){
    echo json_encode(["message"=> "Registration successful"]);
}else{
    echo json_encode(["error=>"=>"User already exists"]);
}
?>