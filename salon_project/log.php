<?php
session_start();
if(isset($_SESSION["user"])){
	header("Location:client/client.php");
	exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chanu Salon - Login</title>
    <link rel="stylesheet" type="text/css" href="log.css"/>
</head>
<body>

    <div class="login-container">
        
        <div class="login-logo">Chanu Salon</div>
        
        <h2>User Login</h2>
        
        <form action="check.php" method="POST"> <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            
            <button type="submit" class="login-btn">Login</button>
            
        </form>
        
        <div class="separator"></div>
        
        <div class="links">
            <a href="register.php">Create an Account (Sign Up)</a>
        </div>
        
    </div>

</body>
</html>



