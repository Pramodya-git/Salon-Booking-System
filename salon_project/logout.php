<?php

session_start();
//$email = $_SESSION["user"]["email"];

if (isset($_SESSION["user"])){
	session_destroy();
	header("Location: log.php");
}
?>