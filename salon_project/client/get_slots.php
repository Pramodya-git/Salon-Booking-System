<?php
// get_slots.php

$conn = new mysqli("localhost","root","","salondb");
if($conn->connect_error){
    die("Connection failed: ". $conn->connect_error);
}

if(isset($_POST['date']) && isset($_POST['duration'])){
    $selectedDate = $_POST['date'];
    $serviceDuration = (int)$_POST['duration']; // E.g., 60
    
    // =========================================================
    // 1. වැඩ කරන වේලාවන් (Working Hours) ලබා ගැනීම (මෙය ස්ථිර හෝ Database එකෙන් ලබා ගත හැක)
    // =========================================================
    // සරල කිරීම සඳහා, මෙහි Hardcode කර ඇත. (ඔබගේ "කාලසටහන් කළමනාකරණය" කොටස ක්‍රියාත්මක නම්, එය භාවිතා කරන්න.)
    $startTime = '09:00:00'; // උදේ 9:00
    $endTime = '18:00:00';   // සවස 6:00
    
    // සතියේ දවස පරීක්ෂා කිරීම (E.g., Sunday off)
    $dayOfWeek = date('w', strtotime($selectedDate)); // 0 = Sunday, 1 = Monday, ...
    
    if ($dayOfWeek == 0) { // Sunday
        echo "<h3>Available Time Slots</h3><p style='color: red;'>Salon is **Closed** on Sundays.</p>";
        $conn->close();
        exit;
    }

    // =========================================================
    // 2. එම දිනයේ දැනටමත් වෙන් කර ඇති වේලාවන් ලබා ගැනීම (Booked Slots)
    // =========================================================
    $bookedSlots = [];
    $sql = "SELECT booking_date, S.duration 
            FROM appointments AS A
            JOIN services AS S ON A.service_id = S.service_id
            WHERE DATE(booking_date) = ? AND A.status != 'Canceled'"; // Canceled නැති bookings
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $selectedDate);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $bookingTime = date('H:i', strtotime($row['booking_date']));
        $slotDuration = (int)$row['duration'];
        
        // එක් එක් Booking එක සඳහා වැය වන සියලුම වේලා කැබලි (Slots) සටහන් කර ගනියි.
        $start = strtotime($selectedDate . ' ' . $bookingTime);
        for ($i = 0; $i < $slotDuration; $i += 30) { // විනාඩි 30ක slots ලෙස සලකයි
             $bookedTimeKey = date('H:i', $start + ($i * 60));
             $bookedSlots[$bookedTimeKey] = true; 
        }
    }
    $stmt->close();
    
    // =========================================================
    // 3. තිබෙන වේලාවන් ගණනය කිරීම (Calculate Available Slots)
    // =========================================================
    $availableSlots = [];
    $currentTime = strtotime($selectedDate . ' ' . $startTime);
    $endTimeStamp = strtotime($selectedDate . ' ' . $endTime);
    $now = time() + (30 * 60); // වෙන්කිරීම සඳහා අවම වශයෙන් විනාඩි 30ක lead time එකක් දෙයි

    // මුළු වැඩ කරන වේලාව (9:00 - 18:00) හරහා ගමන් කිරීම
    while ($currentTime + ($serviceDuration * 60) <= $endTimeStamp) {
        
        $slotStart = date('H:i', $currentTime);
        $slotEnd = date('H:i', $currentTime + ($serviceDuration * 60));
        
        // 3.1. අතීත වේලාවන් ඉවත් කිරීම
        if ($currentTime < $now && date('Y-m-d', $currentTime) == date('Y-m-d')) {
             $currentTime += 30 * 60; // ඊළඟ මිනිත්තු 30 slot එකට යයි
             continue;
        }

        // 3.2. වෙන්කර ඇතිදැයි පරීක්ෂා කිරීම (Overlap Check)
        $isBooked = false;
        // සේවාවට අදාළ මුළු කාලය (serviceDuration) තුළ bookedSlots තිබේදැයි පරීක්ෂා කරයි
        $checkTime = $currentTime;
        while ($checkTime < $currentTime + ($serviceDuration * 60)) {
            $checkTimeKey = date('H:i', $checkTime);
            if (isset($bookedSlots[$checkTimeKey])) {
                $isBooked = true;
                break;
            }
            $checkTime += 30 * 60; // මිනිත්තු 30 බැගින් පරීක්ෂා කරයි
        }
        
        // 3.3. තිබේ නම් එකතු කිරීම
        if (!$isBooked) {
            $availableSlots[$slotStart] = $slotStart; 
        }
        
        // ඊළඟ මිනිත්තු 30 slot එකට යයි
        $currentTime += 30 * 60; 
    }

    // =========================================================
    // 4. ප්‍රතිඵල පෙන්වීම
    // =========================================================
    echo "<h3>Available Time Slots for {$selectedDate} (Duration: {$serviceDuration} min)</h3>";
    
    if (count($availableSlots) > 0) {
        echo "<div class='slot-buttons'>";
        foreach ($availableSlots as $time) {
            // වේලාව තේරීමට button/radio button එකක් පෙන්වයි
            echo "<button type='button' class='time-slot-btn' onclick='selectSlot(\"{$time}\")'>{$time}</button>";
        }
        echo "</div>";
        echo "<input type='hidden' name='bookingTime' id='selectedBookingTime'>";
        // අතිරේක JavaScript: තෝරාගත් වේලාව Hidden Field එකට ඇතුළු කිරීමට
        echo "<script>
                  function selectSlot(time) {
                      document.getElementById('selectedBookingTime').value = time;
                      document.querySelectorAll('.time-slot-btn').forEach(btn => btn.classList.remove('selected'));
                      event.target.classList.add('selected');
                  }
              </script>";
    } else {
        echo "<p>No available slots found for the selected date and service. Please try another date or service.</p>";
    }

} else {
    echo "<h3>Available Time Slots</h3><p>Invalid request.</p>";
}

$conn->close();
?>