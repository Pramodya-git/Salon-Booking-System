<?php
session_start();

// 1. පද්ධතියට ලොග් වී ඇත්දැයි පරීක්ෂා කිරීම
if (!isset($_SESSION["user"])) {
    // If not logged in, redirect to login page
    header("Location: ../log.php");
    exit;
}

// 2. Database Connection
$conn = new mysqli("localhost", "root", "", "salondb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 3. POST දත්ත පරීක්ෂා කිරීම
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // අවශ්‍ය දත්ත ලබා ගැනීම
    $user_id = $_SESSION['user']['user_id']; // Logged-in user's ID
    $booking_date = $_POST['bookingDate'];
    $service_id = $_POST['bookingService'];
    $booking_time = $_POST['bookingTime']; // The selected start time (H:i:s)

    // දිනය සහ වේලාව එකට එකතු කිරීම
    $full_booking_datetime = $booking_date . ' ' . $booking_time;
    
    // *සටහන: serviceDuration මෙහිදී අවශ්‍ය නැත, එය client.php හි slot generation සඳහා පමණක් භාවිතා වේ.*

    // 4. දත්ත ඇතුළු කිරීමේ SQL Query එක
    // Default status should be 'Pending' or 'Confirmed' based on your system design.
    $status = 'Pending'; 
    $booking_sql = "INSERT INTO appointments (user_id, service_id, booking_date, status) VALUES (?, ?, ?, ?)";
    
    $stmt = $conn->prepare($booking_sql);
    
    // Check if the prepare statement failed
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    $stmt->bind_param("iiss", $user_id, $service_id, $full_booking_datetime, $status);

    if ($stmt->execute()) {
        // 5. සාර්ථක වූ පසු, customer.php හෝ success page එකකට යොමු කිරීම
        $_SESSION['booking_success'] = "Appointment successfully requested for " . date('Y-m-d', strtotime($full_booking_datetime)) . " at " . date('g:i A', strtotime($full_booking_datetime));
        header("Location: client.php?status=success");
        exit;
    } else {
        // 6. දෝෂයක් ඇති වුවහොත්
        $_SESSION['booking_error'] = "Error booking appointment: " . htmlspecialchars($stmt->error);
        header("Location: client.php?status=error");
        exit;
    }

    $stmt->close();
} else {
    // GET request එකක් නම්, client page එකට යොමු කිරීම
    header("Location: client.php");
    exit;
}

$conn->close();
?>