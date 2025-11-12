<?php
session_start();
$name = $_POST["username"];
$password = $_POST["password"];

$conn = new mysqli("localhost","root","","salondb");
if($conn->connect_error){
    die("Connection failed: ". $conn->connect_error);
}

// Correct table name -> users
$sql = "select * from users where user_name='$name' and password=SHA2('$password',256)";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {	
    $_SESSION["user"]= $result->fetch_assoc();
	if($_SESSION["user"]["role"]=="client"){
		header("Location:client/client.php");
        exit();
	}else if($_SESSION["user"]["role"]=="admin"){
		header("Location: admin/admin.php");
		exit();
    }else{
        header("Location: log.php?error=Inavalid.");
}
}else{
	header("Location: log.php?error=Inavalid.");
}
?>
