<?php
require_once 'db.php';
$email=$_POST['email'];
$password=$_POST['password'];

$stmt=$conn->prepare(
    "SELECT id, password FROM users WHERE email=?"
);
$stmt->bind_param("s",$email);
$stmt->execute();
$result=$stmt->get_result();
$user=$result->fetch_assoc();

if ($user && password_verify($password,$user['password'])){
    //Generate token
    $token = bin2hex(random_bytes(32));
    $expires=date('Y-m-d H:i:s',strtotime('+1 day'));

     //save token to DB
     $stmt2=$conn->prepare(
        "INSERT INTO sessions (user_id,token,expires_at)VALUES(?,?,?)"
     );
     $stmt2->bind_param("iss", $user['id'],$token,$expires);
     $stmt2->execute();

     echo json_encode(["token"=>$token]);
}else{
    echo json_encode(["error"=>"Invalid credentials"]);
}
?>