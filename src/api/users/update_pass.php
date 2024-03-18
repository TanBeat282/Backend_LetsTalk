<?php
require_once('../../config/Dbhelper.php');

$user_id = $_GET['user_id'];
$password = $_GET['password'];
$decodedPass = base64_decode($password);

// Kiểm tra xác thực người dùng
$sqlCheckUser = "SELECT * FROM users WHERE user_id = '$user_id'";
$resultCheckUser = $conn->query($sqlCheckUser);

if ($resultCheckUser) {
    if ($resultCheckUser->num_rows > 0) {
        $sqlUpdatePassword = "UPDATE users SET password = '$decodedPass' WHERE user_id = '$user_id'";

        if ($conn->query($sqlUpdatePassword) === TRUE) {
            $response = array(
                'status' => true,
                'message' => 'Password updated successfully'
            );
        } else {
            $response = array(
                'status' => false,
                'message' => 'Error updating password: ' . $conn->error
            );
        }
    } else {
        // Người dùng không tồn tại
        $response = array(
            'status' => false,
            'message' => 'User not found'
        );
    }
} else {
    // Lỗi truy vấn cơ sở dữ liệu
    $response = array(
        'status' => false,
        'message' => 'Error querying database'
    );
}

echo json_encode($response);
$conn->close();
