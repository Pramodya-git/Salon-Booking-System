<?php

$conn = new mysqli("localhost","root","","salondb");
if($conn->connect_error){
    die("Connection failed: ". $conn->connect_error);
}
if(isset($_POST["register-details"])){
	$UserName = $_POST['name'];
	$Role = 'client';
	$Contact_Number = $_POST['mobile']; 
	$Email = $_POST['email'];
	$Password = $_POST['password'];
	$Confirm_Password = $_POST['confirm_password'];
	
	if($Password != $Confirm_Password){
		echo "<script>alert('Confirm Password is Wrong.')</script>";
	}
	
	$check_sql = "select count(*) as count_all,
	                    sum(case when user_name = ? then 1 else 0 end) as count_user,
						sum(case when contact_number = ? then 1 else 0 end) as count_mobile,
						sum(case when email = ? then 1 else 0 end) as count_email,
						sum(case when password = ? then 1 else 0 end) as count_password
					    from users
						where user_name = ? or contact_number = ? or email = ? or password = ?";
	
	$stmt = $conn->prepare($check_sql);
	$stmt->bind_param("ssssssss", $UserName, $Contact_Number, $Email, $Password, $UserName, $Contact_Number, $Email, $Password);
	$stmt->execute();
	$result = $stmt->get_result();
	$row = $result->fetch_assoc();
	$stmt->close();
	
	$error_message = "";
	if($row['count_user'] > 0){
		$error_message .= "User Name is already taken.";
	}
	if($row['count_mobile'] > 0){
		$error_message .= "Contact number is already registered.";
	}
	if($row['count_email'] > 0){
		$error_message .= "Email is already registered.";
	}
	if($row['count_password'] > 0){
		$error_message .= "Password is already registered.";
	}
	
if(!empty($error_message)){
	echo "<script>alert('" . htmlspecialchars($error_message, ENT_QUOTES) . "');window.location.href='register.php'</script>";
}
	$insert_sql = "insert into users(role,user_name,contact_number,email,password,registration_date)values(?, ?, ?, ?,SHA2( ?,256),NOW())";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("sssss", $Role, $UserName, $Contact_Number, $Email, $Password);
    
	if ($insert_stmt->execute()) {
        echo "<script>
		            alert('Registration Successful! You can now log in.');
			        window.location.href='log.php';
			  </script>";
		
    } else {
        echo "Error: " . $insert_stmt->error;
    }

    $insert_stmt->close();
}



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chanu Salon - Sign Up</title>
    <link rel="stylesheet" type="text/css" href="register.css" />
</head>
<body>

    <div class="signup-container">
        
        <a href="home_interface/home.html" style="float: left; text-decoration: none; color: #cc6699; font-size: 14px;">&larr; Back to Home</a>
        <div class="signup-logo">Chanu Salon</div>
        
        <h2>Create Your Account</h2>
        
        <form action="register.php" method="POST"> 
            
            <div class="form-group">
                <label for="name">User Name</label>
                <input type="text" id="name" name="name" placeholder="Enter your user name" required>
            </div>
            <input type="hidden" name="user-role" value="client">
            <div class="form-group">
                <label for="mobile">Mobile Number</label>
                <input type="text" id="mobile" name="mobile" placeholder="E.g., 071xxxxxxx" required pattern="[0-9]{10}">
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter a valid email address" required>
            </div>
            
            <div class="form-group">
                <label for="password">Choose Password</label>
                <input type="password" id="password" name="password" placeholder="Minimum 6 characters" required minlength="6">
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter password" required>
            </div>
            
            <button type="submit" name="register-details" class="signup-btn">Register Account</button>
            
        </form>
        
        <div class="links">
            Already have an account? <a href="log.php">Login Here</a>
        </div>
        
    </div>

    <script>
        // Password Match Validation (Client-side)
        document.getElementById('password').addEventListener('input', validatePassword);
        document.getElementById('confirm_password').addEventListener('input', validatePassword);

        function validatePassword() {
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            if (password.value != confirmPassword.value) {
                confirmPassword.setCustomValidity("Passwords Don't Match");
            } else {
                confirmPassword.setCustomValidity('');
            }
        }
    </script>

</body>
</html>