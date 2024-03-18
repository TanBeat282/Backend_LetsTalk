<?php
require_once('../../config/Dbhelper.php');

$user_id = $_GET['user_id'];
$email = $_GET['email'];
$otp = $_GET['otp'];
$time = $_GET['time'];

// Kiểm tra xem đã có dữ liệu tương ứng và thời gian hợp lệ hay không
$sqlCheck = "SELECT * FROM otp WHERE user_id = '$user_id' AND email = '$email' AND otp = '$otp' AND STR_TO_DATE(time, '%Y-%m-%d %H:%i:%s') >= STR_TO_DATE('$time', '%Y-%m-%d %H:%i:%s')";
$resultCheck = $conn->query($sqlCheck);

if ($resultCheck) {
    if ($resultCheck->num_rows > 0) {
        // Xác nhận thành công, xóa dữ liệu trong bảng otp
        $sqlDeleteOtp = "DELETE FROM otp WHERE user_id = '$user_id' AND email = '$email' AND otp = '$otp'";
        if ($conn->query($sqlDeleteOtp) === TRUE) {
            $response = array(
                'status' => true,
                'message' => 'Verify',
                'type' => '1'
            );
        } else {
            $response = array(
                'status' => false,
                'message' => 'Error deleting OTP: ' . $conn->error,
                'type' => '0'
            );
        }
    } else {
        // Kiểm tra xem có dữ liệu với OTP giống nhưng hết thời gian hiệu lực hay không
        $sqlCheckExpired = "SELECT * FROM otp WHERE user_id = '$user_id' AND email = '$email' AND otp = '$otp' AND time >= '$time'";
        $resultCheckExpired = $conn->query($sqlCheckExpired);

        if ($resultCheckExpired->num_rows > 0) {
            // Hết thời gian hiệu lực
            $response = array(
                'status' => false,
                'message' => 'OTP expired',
                'type' => '-1'
            );
        } else {
            // OTP không giống
            $response = array(
                'status' => false,
                'message' => 'Invalid OTP',
                'type' => '0'
            );
        }
    }
} else {
    // Lỗi truy vấn cơ sở dữ liệu
    $response = array(
        'status' => false,
        'message' => 'Error querying database',
        'type' => '0'
    );
}

echo json_encode($response);
$conn->close();
?>
