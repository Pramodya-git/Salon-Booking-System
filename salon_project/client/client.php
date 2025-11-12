<?php
session_start();
// Check if user is logged in
if(!isset($_SESSION["user"])){
	header("Location: ../log.php");
	exit;
}

// Database Connection
$conn = new mysqli("localhost","root","","salondb");
if($conn->connect_error){
	die("Connection failed: ". $conn->connect_error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Chanu Salon</title>
	<link rel="stylesheet" type="text/css" href="client.css"/>	
</head>
<body>

	<div class="main-content" id="mainContent">
		
		<header class="header">
			<div class="logo">Chanu Salon</div>
			<nav class="nav">
				<a href="#">Home</a>
				<a href="#services">Services</a>
				<a href="#info-preview">About Us</a>
				<a href="#info-preview">Contact Us</a>
			</nav>
			<div class="auth-buttons">
				<span class="login-user"><?php echo htmlspecialchars($_SESSION['user']['user_name']); echo '('.htmlspecialchars($_SESSION['user']['role']).')'; ?></span>
				<a href="../logout.php" style="color:white;text-decoration:none;background-color:#cc6699;padding:9px;border-radius:5px;">Log Out</a>
			</div>
		</header>

		<section class="hero">
			<h1>Your Beauty Journey Starts Here</h1>
			<p style="font-size: 18px; margin-bottom: 30px;">Book a convenient time slot today. No queues, no delays!</p>
			<a href="#services" class="book-btn" >Book Now</a>
		</section>

		<section class="services-preview" id="services">
			<h2>Our Popular Services</h2>
			
			<div class="service-cards horizontal-scroll-container">
				<?php					
				$rch = "SELECT * FROM services";
				$resc = $conn->query($rch);
				if($resc->num_rows > 0){
					while($ret = $resc->fetch_assoc()){
						?>
						<div class="card">
							<div class="card-image-section card-image-haircut" >
								<img
									src="../admin/Test Image/<?php echo htmlspecialchars($ret["photo"]) ?>"
									alt="<?php echo htmlspecialchars($ret['service_name']); ?> Service Image"
									class="card-image"
									onerror="this.onerror=null; this.src='https://placehold.co/400x250/f0f0f0/333?text=Image+Missing'"
								>
							</div>
							<div class="card-content">
								<h3><?php echo htmlspecialchars($ret['service_name']); ?></h3>
								<p><?php echo htmlspecialchars($ret['description']); ?></p>
								<p><?php echo htmlspecialchars($ret['duration']); ?> Minutes</p>
								<span class="price"><?php echo htmlspecialchars($ret['price']); ?></span>
							</div>
						</div>
						<?php
					}}
				?>
			</div>
			
            
             <h2>Book Your Appointment</h2>
             
               <div class="booking-section" id="booking-form">
                   <h2>Book Your Appointment</h2>
                    <form id="bookingForm" action="process_booking.php" method="POST">
					<input type="hidden" id="bookingTimeHidden" name="bookingTime" value=""> <div class="form-group">
						<label for="bookingDateInput">දිනය (Date)</label>
						<input type="date" id="bookingDateInput" name="bookingDateInput"
							onchange="document.getElementById('bookingDateHidden').value = this.value; this.form.submit();"
							required>
					</div>
					
					<div class="form-group">
						<label for="bookingServiceInput">සේවාව (Service)</label>
						<select id="bookingServiceInput" name="bookingServiceInput" onchange="calculateTimeSlot()" required>
						    <option>--Select Service--</option>
							<?php
                                $data_sql = "select service_name,duration,price,category from services";
                                $result = $conn->query($data_sql);
				                if($result->num_rows > 0){
					                    while($ret = $result->fetch_assoc()){								
							                    ?>
												<option value="<?php echo htmlspecialchars($ret['service_name']) ." ". htmlspecialchars($ret['duration']) . htmlspecialchars($ret['price']) . htmlspecialchars($ret['category'])?>" > <?php echo htmlspecialchars($ret['service_name']) ." ". '(' . htmlspecialchars($ret['duration'])." Minutes" . ')'." " . 'Rs.' . htmlspecialchars($ret['price']) . ' - ' . htmlspecialchars($ret['category']) ?> </option>
										        <?php
								            }
								}
							?>
							
						</select>
					</div>
					
					<div class="form-group" id="timeSlotContainer">
						<label>වේලාව (Time Slot)</label>
						<div class="time-slots-wrapper">
						</div>
					</div>

					<button type="submit" class="submit-btn" id="confirmBookingBtn" disabled>Confirm Appointment</button>
					<p style="margin-top: 10px; color: #888; font-size: small;" id="bookingHelperText">Select date, service, and a time slot above to enable booking.</p>
					
				</form>
			</div>
			</section>

		<section class="info-preview" id="info-preview">
			<div class="info-cards">
				<div class="info-card about">
					<h3>About Chanu Salon</h3>
					<p>We are the leading beauty salon in Kuliyapitiya, dedicated to providing high-quality treatments and exceptional customer service.</p>
					<a href="#" class="info-link">Learn More &rarr;</a>
				</div>
				<div class="info-card contact" >
					<h3>Contact Us</h3>
					<p>Book your appointment or inquire about our services via phone or email. We are here to help you!</p>
					<a href="#" class="info-link">Get in Touch &rarr;</a>
				</div>
			</div>
		</section>

		<footer class="footer">
			<p>&copy; 2025 Chanu Salon. All Rights Reserved. | Kuliyapitiya.</p>
			<p class="tech-info">Project developed using WAMP Server, MySQL, and Note Pad++.</p>
		</footer>

	</div>
	
	<script>
		function selectTimeSlot(time, buttonElement) {
			// 1. Update the hidden input field with the selected time
			document.getElementById('bookingTimeHidden').value = time;

			// 2. Remove 'selected' class from all buttons
			const buttons = document.querySelectorAll('.time-slot-btn');
			buttons.forEach(btn => btn.classList.remove('selected'));

			// 3. Add 'selected' class to the clicked button
			buttonElement.classList.add('selected');

			// 4. Enable the confirm button and style it
			const confirmBtn = document.getElementById('confirmBookingBtn');
			confirmBtn.disabled = false;
			confirmBtn.style.backgroundColor = '#cc6699';
			confirmBtn.style.cursor = 'pointer';
		}

		// Initial check for the button state
		document.addEventListener('DOMContentLoaded', function() {
			const timeSlotHidden = document.getElementById('bookingTimeHidden');
			const confirmBtn = document.getElementById('confirmBookingBtn');
			
			if (timeSlotHidden.value !== "") {
				confirmBtn.disabled = false;
				confirmBtn.style.backgroundColor = '#cc6699';
				confirmBtn.style.cursor = 'pointer';

				const time = timeSlotHidden.value;
				const buttons = document.querySelectorAll('.time-slot-btn');
				buttons.forEach(btn => {
					if (btn.getAttribute('onclick').includes("'" + time + "'")) {
						btn.classList.add('selected');
					}
				});
			}
		});
	</script>
	
</body>
</html>