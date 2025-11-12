<?php
session_start();
if(!isset($_SESSION["user"])){
	header("Location: ../log.php");
	exit;
}

$conn = new mysqli("localhost","root","","salondb");
if($conn->connect_error){
    die("Connection failed: ". $conn->connect_error);
}
if(isset($_POST['submit'])){
    $ServiceName = $_POST['serviceName'];
    $ServiceDuration = $_POST['serviceDuration'];
    $ServicePrice = $_POST['servicePrice'];
    $ServiceCategory = $_POST['serviceCategory'];
    $ServiceDescription = $_POST['serviceDescription'];
    
    // Hidden field එකෙන් ID එක ලබා ගැනීම.
    // **සටහන: ඔබගේ HTML Form එකේ hidden field එකේ name="serviceID" ලෙස ඇත. 
    // මෙහිදී 'editServiceId' වෙනුවට 'serviceID' ලෙස වෙනස් කර ඇත.**
    $EditServiceId = trim($_POST['serviceID']); 
    
    $file_name = $_FILES['photo']['name'];
    $tempname = $_FILES['photo']['tmp_name'];
    $folder = 'Test Image/'.$file_name; 

    $query_success = false;
    $is_update = !empty($EditServiceId); // ID එකක් තිබේදැයි පරීක්ෂා කරයි

    if ($is_update) {
        // 1. UPDATE Logic
        $stmt = null;
        if (empty($file_name)) {
            // ඡායාරූපය වෙනස් නොකරන්නේ නම්
            $sql = "UPDATE services SET service_name=?, description=?, duration=?, price=?, category=? WHERE service_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssidsi", $ServiceName, $ServiceDescription, $ServiceDuration, $ServicePrice, $ServiceCategory, $EditServiceId);
            
        } else {
            // ඡායාරූපයද වෙනස් කරන්නේ නම්
            $sql = "UPDATE services SET service_name=?, description=?, duration=?, price=?, category=?, photo=? WHERE service_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssidssi", $ServiceName, $ServiceDescription, $ServiceDuration, $ServicePrice, $ServiceCategory, $file_name, $EditServiceId);
        }

    } else {	
        // 2. INSERT Logic (නව සේවාවක් ඇතුළු කිරීම) - Prepared Statement භාවිතයෙන් නිවැරදි කර ඇත
        $sql = "INSERT INTO services (service_name, description, duration, price, category, photo) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssidss", $ServiceName, $ServiceDescription, $ServiceDuration, $ServicePrice, $ServiceCategory, $file_name);
    }
    
    // Query Execute කිරීම සහ ප්‍රතිඵලය පරීක්ෂා කිරීම (නව කොටස)

    $upload_successful = true;

    if (isset($stmt)) {
        if ($stmt->execute()) {
            $query_success = true;
        } else {
            echo "Error executing database operation: " . $stmt->error;
        }
        $stmt->close();
    }
    
    // ගොනුව උඩුගත කිරීම (Upload) - Query එක සාර්ථක නම් පමණක්
    if (!empty($file_name) && $query_success) {
        if (!move_uploaded_file($tempname, $folder)) {
            $upload_successful = false;
        }
    }

    // අවසාන ප්‍රතිඵලය පෙන්වීම
    if ($query_success && $upload_successful) {
        $message = $is_update ? 'Service Updated Successfully!' : 'Service Added Successfully!';
        echo "<script>
                  alert('".$message."');
                  window.location.href='admin.php#services-manage';  // #services-manage වෙත යොමු කරයි
              </script>";
    } else if ($query_success && !$upload_successful) {
        $message = $is_update ? 'Service Updated, but image failed to upload!' : 'Service Added, but image failed to upload!';
        echo "<script>
                  alert('".$message."');
                  window.location.href='admin.php#services-manage';
               </script>";
    } else {
        echo "<script>
                  alert('Operation Unsuccessful!');
                  window.location.href='admin.php#services-manage';
              </script>";
    }
}
if(isset($_POST['user-submit'])){
    $CustName = $_POST['custName'];
    $CustRole = $_POST['custRole'];
    $CustEmail = $_POST['custEmail'];
    $CustMobile = $_POST['custMobile'];
	$CustRegistrationDate = $_POST['custRegistrationDate'];
    
    // Hidden field එකෙන් ID එක ලබා ගැනීම.
    // **සටහන: ඔබගේ HTML Form එකේ hidden field එකේ name="serviceID" ලෙස ඇත. 
    // මෙහිදී 'editUserId' වෙනුවට 'userID' ලෙස වෙනස් කර ඇත.**
    $EditUserId = trim($_POST['custID']); 
     

    $query_success = false;
    $is_update = !empty($EditUserId); // ID එකක් තිබේදැයි පරීක්ෂා කරයි

    if ($is_update) {
        // 1. UPDATE Logic
        $stmt = null;
        
        $sql = "UPDATE users SET user_name=?, role=?, contact_number=?, email=?, registration_date=? WHERE user_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $CustName, $CustRole, $CustMobile, $CustEmail, $CustRegistrationDate, $EditUserId);
        

    } else {	
        // 2. INSERT Logic (නව සේවාවක් ඇතුළු කිරීම) - Prepared Statement භාවිතයෙන් නිවැරදි කර ඇත
        $sql = "INSERT INTO users (user_name, role, contact_number, email, registration_date) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $CustName, $CustRole, $CustMobile, $CustEmail, $CustRegistrationDate);
    }
    
    // Query Execute කිරීම සහ ප්‍රතිඵලය පරීක්ෂා කිරීම (නව කොටස)

    $upload_successful = true;

    if (isset($stmt)) {
        if ($stmt->execute()) {
            $query_success = true;
        } else {
            echo "Error executing database operation: " . $stmt->error;
        }
        $stmt->close();
    }
    

    // අවසාන ප්‍රතිඵලය පෙන්වීම
    if ($query_success && $upload_successful) {
        $message = $is_update ? 'User Updated Successfully!' : 'User Added Successfully!';
        echo "<script>
                  alert('".$message."');
                  window.location.href='admin.php#customers';  // #customers වෙත යොමු කරයි
              </script>";
    } else if ($query_success && !$upload_successful) {
        $message = $is_update ? 'User Updated!' : 'User Added!';
        echo "<script>
                  alert('".$message."');
                  window.location.href='admin.php#customers';
               </script>";
    } else {
        echo "<script>
                  alert('Operation Unsuccessful!');
                  window.location.href='admin.php#customers';
              </script>";
    }
}

if(isset($_POST["detail-submit"])){
$EditBookingId = $_POST['editBookingId'];
$EditCustomer = $_POST['editCustomer'];
$EditService = $_POST['editService'];
$EditDate = $_POST['editDate'];
$EditTime = $_POST['editTime'];
$EditStatus = $_POST['editStatus'];

$booking_date = $EditDate . ' ' . $EditTime;

// Build query
    $update_sql = "UPDATE
        appointments AS A,
        users AS U,
        services AS S
    SET
        A.user_id = U.user_id,          -- username එකට අදාළ user_id සොයාගෙන update කරයි
        A.service_id = S.service_id,    -- service_name එකට අදාළ service_id සොයාගෙන update කරයි
        A.status = '$EditStatus',             -- status එක සෘජුවම update කරයි
        A.booking_date = '$booking_date'  -- booking_date එක සෘජුවම update කරයි
    WHERE
        -- A සහ U සම්බන්ධ කිරීම
        U.user_name = '$EditCustomer'
        
        -- A සහ S සම්බන්ධ කිරීම
        AND S.service_name = '$EditService'
        
        -- යාවත්කාලීන කළ යුතු නිශ්චිත row එක
        AND A.appointment_id = '$EditBookingId';";
    
    // Execute query
    if($conn->query($update_sql)){
        // Move uploaded file if provided
        echo "<script>
                alert('Booking Details Updated Successfully!');
                window.location.href='admin.php';
              </script>";
    } else {
        echo "Update failed: " . $conn->error;
    }
	
}
if(isset($_POST["time-table-details"])){
	$WorkingDay = $_POST['workingDay'];
	$StartTime = $_POST['startTime'];
	$EndTime = $_POST['endTime'];
	$Is_working = $_POST['is_working'];
	$UserID = $_POST['userid'];
	
	$sql = "insert into working_hours(day_of_week,start_time,end_time,is_working,user_id)values('$WorkingDay','$StartTime','$EndTime','$Is_working','$UserID')";
	$result = $conn->query($sql);
	if($result){
		echo "<script>
                  alert('Working Hours Records Added Successfully!');
                  window.location.href='admin.php';
              </script>";
	}else{
		echo "<script>
                  alert('Working Hours Records Added Unsuccessfully!');
                  window.location.href='admin.php';
              </script>";
	}
}
if(isset($_POST['delete'])){
    $Service_id = $_POST['ServiceID'];
	
	$conn->query("DELETE FROM appointments WHERE service_id = '$Service_id'");
	if($conn->query("DELETE FROM services WHERE service_id = '$Service_id'")){
        echo "<script>alert('Services $Service_id details deleted successfully');</script>";
        echo "<script>window.location.href = 'admin.php';</script>";
    } else {
        echo "Error deleting services: " . $conn->error;
    }
}
if(isset($_POST['user-delete'])){
	$userid = $_POST['UserID'];
	$conn->query("DELETE FROM appointments WHERE user_id = '$userid'");
	$conn->query("DELETE FROM working_hours WHERE user_id = '$userid'");
	if($conn->query("DELETE FROM users WHERE user_id = '$userid'")){
        echo "<script>alert('User $userid details deleted successfully');</script>";
        echo "<script>window.location.href = 'admin.php';</script>";
    } else {
        echo "Error deleting user: " . $conn->error;
    }
	
}
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>පරිපාලක උපකරණ පුවරුව | Chanu Salon</title>
    <link rel="stylesheet" type="text/css" href="admin.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-logo">Chanu Admin</div>
        <div class="sidebar-menu">
            <a href="#dashboard"><i class="fas fa-tachometer-alt"></i> උපකරණ පුවරුව</a>
            <a href="#bookings"><i class="fas fa-calendar-check"></i> වෙන්කිරීම් කළමනාකරණය</a>
            <a href="#services-manage"><i class="fas fa-list-alt"></i> සේවා කළමනාකරණය</a>
            <a href="#schedule-manage"><i class="fas fa-clock"></i> කාලසටහන් කළමනාකරණය</a>
            <a href="#customers"><i class="fas fa-users"></i> පාරිභෝගික තොරතුරු</a>
        </div>
    </div>

    <div class="main-content">
        
        <div class="top-bar">
            <h1>පරිපාලක ද්වාරය</h1>
            <div class="user-info">
                <span><?php echo $_SESSION['user']['role'].':'.$_SESSION['user']['user_name'].'['.$_SESSION['user']['user_id'].']';?></span>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> පිටවීම</a>
            </div>
        </div>

        <section class="content-section" id="dashboard">
            <h2><i class="fas fa-tachometer-alt"></i> Dashboard Overview</h2>
            
            <div class="stats-grid">
                <div class="stat-card"><h3>අද වෙන්කිරීම්</h3><div class="value"><?php  $sql = "SELECT COUNT(appointment_id) AS total_appointment FROM appointments WHERE DATE(booking_date) = CURDATE() and status = 'Confirmed'";
                            $result = $conn->query($sql); $total = $result->fetch_assoc(); echo $total['total_appointment']; ?></div></div>
                <div class="stat-card" style="border-left-color: #007bff;"><h3>ඉදිරියේදී ඇති වෙන්කිරීම්</h3><div class="value"><?php $sql = "select count(appointment_id) as total_allappointment from appointments where DATE(booking_date) > CURDATE() and status = 'Confirmed'";
                            $result = $conn->query($sql); $total = $result->fetch_assoc(); echo $total['total_allappointment']; ?></div></div>
                <div class="stat-card" style="border-left-color: #ffc107;"><h3>නව පාරිභෝගිකයන් (මාසික)</h3><div class="value"><?php $sql = "select count(user_id) as total_cusers from users where role='client'";
                            $result = $conn->query($sql); $total = $result->fetch_assoc(); echo $total['total_cusers']; ?></div></div>
                <div class="stat-card" style="border-left-color: var(--admin-secondary);"><h3>මුළු සේවා</h3><div class="value"><?php $sql = "select count(service_id) as total_services from services";
                            $result = $conn->query($sql); $total = $result->fetch_assoc(); echo $total['total_services']; ?></div></div>
            </div>

            <h3 style="margin-top: 30px; color: var(--admin-primary);">අද දින වෙන්කිරීම් කාලසටහන <span id="todayDate"></span>
                <script>
                    document.getElementById('todayDate').innerHTML = new Date().toLocaleDateString();
                </script>
			</h3>
            <div style="max-height: 350px; overflow-y: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th><center>වේලාව</center></th>
                            <th><center>සේවාව</center></th>
                            <th><center>පාරිභෝගිකයා</center></th>
                            <th><center>තත්ත්වය</center></th>
                        </tr>
                    </thead>
					<div class="scrollable-content">
                    <tbody>
                        <?php                   
                        //Search bar variable
							$rch = "select appointments.booking_date,users.user_name,services.service_name,appointments.status from ((appointments inner join services on appointments.service_id = services.service_id)inner join users on appointments.user_id = users.user_id) WHERE DATE(booking_date) = CURDATE()";
							$resc =  $conn->query($rch);
									if($resc->num_rows > 0){
										while($ret = $resc->fetch_assoc()){
												?>
												<tr>
						                              <td><center><?php echo $ret["booking_date"] ?></center></td>
							                          <td><center><?php echo $ret["service_name"] ?></center></td>
							                          <td><center><?php echo $ret["user_name"] ?></center></td>
							                          <td><center><?php echo $ret["status"] ?></center></td>
						                        </tr>
												<?php
										}
									}else{
										?>
										<tr><td colspan="5"><center>No Records Found.</center></td></tr>
										<?php
									}
                                
                        ?>
                    </tbody>
					</div>
                </table>
            </div>
        </section>


        <section class="content-section" id="bookings">
            <h2><i class="fas fa-calendar-alt"></i> වෙන්කිරීම් කළමනාකරණය</h2>
            
            <div style="max-height: 500px; overflow-y: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th><center>Booking ID</center></th>
                            <th><center>දිනය/වේලාව</center></th>
                            <th><center>පාරිභෝගිකයා</center></th>
                            <th><center>සේවාව</center></th>
                            <th><center>තත්ත්වය</center></th>
                
                        </tr>
                    </thead>
					<div class="scrollable-content">
                    <tbody>
						<?php                   
                        //Search bar variable
							$rch = "select appointments.appointment_id,appointments.booking_date,users.user_name,services.service_name,appointments.status from ((appointments inner join services on appointments.service_id = services.service_id)inner join users on appointments.user_id = users.user_id)";
							$resc =  $conn->query($rch);
									if($resc->num_rows > 0){
										while($ret = $resc->fetch_assoc()){
												?>
												<tr>
						                              <td><center><?php echo $ret["appointment_id"] ?></center></td>
							                          <td><center><?php echo $ret["booking_date"] ?></center></td>
							                          <td><center><?php echo $ret["user_name"] ?></center></td>
							                          <td><center><?php echo $ret["service_name"] ?></center></td>
							                          <td><center><?php echo $ret["status"] ?></center></td>
						                        </tr>
												<?php
										}
									}else{
										?>
										<tr><td colspan="5"><center>No Records Found.</center></td></tr>
										<?php
									}
                                
                                                 ?>	
                    </tbody>
					</div>
                </table>
            </div>
            
            <h3 id="booking-form-edit" style="margin-top: 30px; color: var(--admin-primary);">වෙන්කිරීමක් සංස්කරණය කරන්න</h3>
            <form class="form-container" action="admin.php" method="post">
                <div class="form-group">
                    <label for="editBookingId">Booking ID</label>
                    <input type="text" name="editBookingId" id="editBookingId"  required>
                </div>
                <div class="form-group">
                    <label for="editCustomer">Customer</label>
                    <input type="text" name="editCustomer" id="editCustomer"  required>
                </div>
                <div class="form-group">
                    <label for="editService">Service</label>
                    <input type="text" name="editService" id="editService" required>
                </div>
                 <div class="form-group">
                    <label for="editDate">Date</label>
                    <input type="date" name="editDate" id="editDate"  required>
                </div>
                 <div class="form-group">
                    <label for="editTime">Time</label>
                    <input type="time" name="editTime" id="editTime" required>
                </div>
                <div class="form-group">
                    <label for="editStatus">Status</label>
                    <select id="editStatus" name="editStatus" required>
                        <option value="Confirmed" selected>Confirmed</option>
                        <option value="Pending">Pending</option>
                        <option value="Canceled">Canceled</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" name="detail-submit" class="btn-save"><i class="fas fa-save"></i> වෙනස්කම් සුරකින්න</button>
                </div>
            </form>
        </section>
        
        <section class="content-section" id="services-manage">
            <h2><i class="fas fa-list-alt"></i> සේවා කළමනාකරණය</h2>
            
            <h3 style="color: var(--admin-primary);">නව සේවාවක් එක් කරන්න</h3>
            <form class="form-container" method="POST" action="admin.php" id="services-manage" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="serviceName">Service Name</label>
                    <input type="text" id="serviceName" name="serviceName" placeholder="E.g., Deep Tissue Facial" required>
                </div>
                    <input type="hidden" id="editServiceId" name="serviceID" >
                <div class="form-group">
                    <label for="serviceDuration">Duration (minutes)</label>
                    <input type="number" id="serviceDuration" name="serviceDuration" placeholder="E.g., 60" required>
                </div>
                <div class="form-group">
                    <label for="servicePrice">Price (Rs.)</label>
                    <input type="number" id="servicePrice" name="servicePrice" placeholder="E.g., 4500.00" required>
                </div>
                <div class="form-group">
                    <label for="serviceCategory">Category</label>
                    <select id="serviceCategory" name="serviceCategory" required>
                        <option value="Hair Treatments">Hair Treatments</option>
                        <option value="Facial & Skin Treatments">Facial & Skin Treatments</option>
                        <option value="Hand & Foot Care">Hand & Foot Care</option>
						<option value="Makeup & Beauty Services ">Makeup & Beauty Services </option>
                    </select>
                </div>
				<div class="form-group">
                    <label for="serviceDescription">Description</label>
                    <textarea type="text" id="serviceDescription" name="serviceDescription"  rows="5" cols="3" required></textarea>
                </div>
				<div class="form-group"><label for="serviceImage">Images</label>
				    <input type="file" id="serviceImage" name="photo" required>
				</div>
                <div class="form-actions">
                    <button type="submit" name="submit" class="btn-save"><i class="fas fa-save"></i> සුරකින්න</button>
                </div>
            </form>
			
			
<script>
    function loadServiceData(buttonElement) {
        // 1. බොත්තමෙන් සියලුම data attributes ලබා ගැනීම
        const serviceId = buttonElement.getAttribute('data-id');
        const serviceName = buttonElement.getAttribute('data-name');
        const serviceDuration = buttonElement.getAttribute('data-duration');
        const servicePrice = buttonElement.getAttribute('data-price');
        const serviceCategory = buttonElement.getAttribute('data-category');
        const serviceDescription = buttonElement.getAttribute('data-description');

        // 2. අදාළ Form Fields හඳුනා ගැනීම
        const formIdField = document.getElementById('editServiceId'); // නව Hidden Field එක
        const nameField = document.getElementById('serviceName');
        const durationField = document.getElementById('serviceDuration');
        const priceField = document.getElementById('servicePrice');
        const categoryField = document.getElementById('serviceCategory');
        const descriptionField = document.getElementById('serviceDescription');

        // 3. Form Fields වලට අගයන් ඇතුළත් කිරීම
        if (formIdField) {
             formIdField.value = serviceId; // සැඟවුණු ID field එකට අගය ඇතුළු කරයි
        }
        nameField.value = serviceName;
        durationField.value = serviceDuration;
        priceField.value = servicePrice;
        categoryField.value = serviceCategory;
        descriptionField.value = serviceDescription;

        // 4. පරිශීලකයා පහසුවෙන් Form එක වෙත යොමු කිරීම
        window.location.href = '#services-manage'; 
        
        // *වෙනස්කම් සුරකින්න* බොත්තමේ නම "Update" ලෙස වෙනස් කිරීම
        const saveButton = document.querySelector('#services-manage .btn-save');
        if (saveButton) {
            saveButton.innerHTML = '<i class="fas fa-save"></i> යාවත්කාලීන කරන්න';
        }

        // Form එකේ මුලට Scroll කිරීම (User experience සඳහා)
        document.getElementById('services-manage').scrollIntoView({ behavior: 'smooth' });
    }
</script>
			
            <h3 style="margin-top: 30px; color: var(--admin-primary);">දැනට පවතින සේවා</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th><center>ID</center></th>
                        <th><center>Name</center></th>
                        <th><center>Duration</center></th>
                        <th><center>Price (Rs.)</center></th>
                        <th><center>Category</center></th>
                        <th><center>Action</center></th>
                    </tr>
                </thead>
                <tbody>
                    <?php                   
                        //Search bar variable
							$rch = "select * from services";
							$resc =  $conn->query($rch);
									if($resc->num_rows > 0){
										while($ret = $resc->fetch_assoc()){
												?>
												<tr>
						                              <td><center><?php echo $ret["service_id"] ?></center></td>
							                          <td><center><?php echo $ret["service_name"] ?></center></td>
							                          <td><center><?php echo $ret["duration"] ?></center></td>
							                          <td><center><?php echo $ret["price"] ?></center></td>
							                          <td><center><?php echo $ret["category"] ?></center></td>
													  <td><center>
																<form method="POST" action="admin.php" style="display:inline;">
																	<input type="hidden" name="ServiceID" value="<?php echo $ret['service_id']; ?>">
																    <input type="submit" name="delete" class="action-btn btn-edit edit-service-btn" value="මකාදමන්න<?php echo $ret['service_id']; ?>">	
															    </form>
															<button type="button" class="action-btn btn-edit edit-service-btn" 
                                                                    onclick="loadServiceData(this)"
                                                                    data-id="<?php echo $ret['service_id']; ?>"
                                                                    data-name="<?php echo htmlspecialchars($ret['service_name']); ?>"
                                                                    data-duration="<?php echo $ret['duration']; ?>"
                                                                    data-price="<?php echo $ret['price']; ?>"
                                                                    data-category="<?php echo $ret['category']; ?>"
                                                                    data-description="<?php echo htmlspecialchars($ret['description']); ?>">
                                                                Edit
                                                            </button>	
															
															
															</center>
													  </td>
						                        </tr>
												<?php
										}
									}else{
										?>
										<tr><td colspan="5"><center>No Records Found.</center></td></tr>
										<?php
									}
                                
                                                 ?>
			   </tbody>
            </table>
        </section>

        <section class="content-section" id="schedule-manage">
            <h2><i class="fas fa-clock"></i> කාලසටහන් කළමනාකරණය</h2>
            
            <form class="form-container" action="admin.php" method="post">
                <div class="form-group">
                    <label for="workingDay">Working Day</label>
                    <select id="workingDay" name="workingDay" required>
                        <option value="monday">Monday</option>
                        <option value="tuesday">Tuesday</option>
						<option value="wednesday">Wednesday</option>
						<option value="thursday">Thursday</option>
						<option value="friday">Friday</option>
						<option value="saturday">Saturday</option>
                        <option value="sunday">Sunday (Closed)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="startTime">Start Time</label>
                    <input type="time" name="startTime" id="startTime" required>
                </div>
                <div class="form-group">
                    <label for="endTime">End Time</label>
                    <input type="time" id="endTime" name="endTime" required>
                </div>
                <div class="form-group">
                    <label for="is_working">IS_Working</label>
                    <select id="is_working" name="is_working" required>
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>
                </div>
				<div class="form-group">
                    <label for="userid">User ID</label>
                    <input type="text" name="userid" id="userid"  required>
                </div>
                <div class="form-actions">
                    <button type="submit" name="time-table-details" class="btn-save">කාලසටහන යාවත්කාලීන කරන්න</button>
                </div>
            </form>
        </section>
        <script>
    function loaduserData(buttonElement) {
    const userId = buttonElement.getAttribute('data-id');
    const userName = buttonElement.getAttribute('data-name');
    const userRole = buttonElement.getAttribute('data-role');
    const userEmail = buttonElement.getAttribute('data-email');
    const userContactNumber = buttonElement.getAttribute('data-contact');
    const userRegistrationDate = buttonElement.getAttribute('data-registration');

    document.getElementById('custID').value = userId;
    document.getElementById('custName').value = userName;
    document.getElementById('custRole').value = userRole;
    document.getElementById('custEmail').value = userEmail;
    document.getElementById('custMobile').value = userContactNumber;
    document.getElementById('custRegistrationDate').value = userRegistrationDate;

    window.location.href = '#customers';

    const saveButton = document.querySelector('#customers .btn-save');
    if (saveButton) {
        saveButton.innerHTML = '<i class="fas fa-save"></i> යාවත්කාලීන කරන්න';
    }

    document.getElementById('customers').scrollIntoView({ behavior: 'smooth' });
}

</script>
        <section class="content-section" id="customers">
            <h2><i class="fas fa-users"></i> පාරිභෝගික තොරතුරු</h2>

            <div style="max-height: 500px; overflow-y: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th><center>ID</center></th>
                            <th><center>Name</center></th>
                            <th><center>Role</center></th>
                            <th><center>Email</center></th>
                            <th><center>Mobile</center></th>
                            <th><center>Joined Date</center></th>
                            <th><center>Action</center></th>
                        </tr>
                    </thead>
                    <tbody>    
                    <?php                   
                        //Search bar variable
							$rch = "select * from users";
							$resc =  $conn->query($rch);
									if($resc->num_rows > 0){
										while($ret = $resc->fetch_assoc()){
												?>
												<tr>
						                              <td><center><?php echo $ret["user_id"] ?></center></td>
							                          <td><center><?php echo $ret["user_name"] ?></center></td>
							                          <td><center><?php echo $ret["role"] ?></center></td>
							                          <td><center><?php echo $ret["email"] ?></center></td>
							                          <td><center><?php echo $ret["contact_number"] ?></center></td>
							                          <td><center><?php echo $ret["registration_date"] ?></center></td>
													  <td><center>
																<form method="POST" action="admin.php" style="display:inline;">
																	<input type="hidden" name="UserID" value="<?php echo $ret['user_id']; ?>">
																    <input type="submit" name="user-delete" class="action-btn btn-edit edit-service-btn" value="මකාදමන්න<?php echo $ret['user_id']; ?>">	
															    </form>
															<button   class="action-btn btn-edit edit-service-btn"
                                                                      type="button" 
                                                                      onclick="loaduserData(this)"
                                                                      data-id="<?php echo $ret['user_id']; ?>"
                                                                      data-name="<?php echo htmlspecialchars($ret['user_name']); ?>"
                                                                      data-role="<?php echo $ret['role']; ?>"
                                                                      data-email="<?php echo $ret['email']; ?>"
                                                                      data-contact="<?php echo $ret['contact_number']; ?>"
                                                                      data-registration="<?php echo $ret['registration_date']; ?>"
                                                                    >
                                                                Edit
                                                            </button>	
															
															
															</center>
													  </td>
						                        </tr>
												<?php
										}
									}else{
										?>
										<tr><td colspan="5"><center>No Records Found.</center></td></tr>
										<?php
									}
                                
                                                 ?>
                    </tbody>
                </table>
            </div>

             <h3 id="customer-form-edit" style="margin-top: 30px; color: var(--admin-primary);">පාරිභෝගික විස්තර සංස්කරණය කරන්න</h3>
            <form class="form-container" action="admin.php" method="post">

                    <input type="hidden" id="custID" name="custID">
                <div class="form-group">
                    <label for="custName">Name</label>
                    <input type="text" id="custName" name="custName" required>
                </div>
                <div class="form-group">
                    <label for="custRole">Role</label>
                    <input type="text" id="custRole" name="custRole" required>
                </div>
                <div class="form-group">
                    <label for="custEmail">Email</label>
                    <input type="email" id="custEmail" name="custEmail" required>
                </div>
                <div class="form-group">
                    <label for="custMobile">Mobile</label>
                    <input type="text" id="custMobile" name="custMobile" required>
                </div>
				<div class="form-group">
                    <label for="custRegistrationDate">Registration Date</label>
                    <input type="text" id="custRegistrationDate" name="custRegistrationDate" required>
                </div>
                <div class="form-actions">
                    <button type="submit" name="user-submit" class="btn-save"><i class="fas fa-save"></i> සුරකින්න</button>
                </div>
            </form>
        </section>

    </div>

 

</body>
</html>