<?php
require_once('../../config/Dbhelper.php');

$user_id = $_GET['user_id'];
$email = $_GET['email'];
$otp = $_GET['otp'];
$time = $_GET['time'];

// Kiểm tra xem đã có dữ liệu tương ứng hay chưa
$sqlCheck = "SELECT * FROM otp WHERE user_id = '$user_id' AND email = '$email'";
$resultCheck = $conn->query($sqlCheck);

if ($resultCheck) {
    if ($resultCheck->num_rows > 0) {
        // Nếu có dữ liệu, thực hiện cập nhật (UPDATE)
        $sqlUpdate = "UPDATE otp SET otp = '$otp', time = '$time' WHERE user_id = '$user_id' AND email = '$email'";
        $resultUpdate = $conn->query($sqlUpdate);

        if ($resultUpdate) {
            $response = array(
                'status' => true,
                'message' => 'Updated successfully'
            );
            echo json_encode($response);
        } else {
            $response = array(
                'status' => false,
                'message' => 'Error updating data'
            );
            echo json_encode($response);
        }
    } else {
        // Nếu không có dữ liệu, thực hiện thêm mới (INSERT)
        $sqlInsert = "INSERT INTO otp (user_id, email, otp, time) VALUES ('$user_id', '$email', '$otp', '$time')";
        $resultInsert = $conn->query($sqlInsert);

        if ($resultInsert) {
            $response = array(
                'status' => true,
                'message' => 'Inserted successfully'
            );
            echo json_encode($response);
        } else {
            $response = array(
                'status' => false,
                'message' => 'Error inserting data'
            );
            echo json_encode($response);
        }
    }
} else {
    $response = array(
        'status' => false,
        'message' => 'Error querying database'
    );
    echo json_encode($response);
}

$conn->close();
?>
