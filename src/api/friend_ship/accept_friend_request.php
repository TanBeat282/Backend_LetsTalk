<?php
require_once('../../config/Dbhelper.php');

$user_id = $_GET['user_id'];
$sender_id = $_GET['sender_id'];
$receiver_id = $_GET['receiver_id'];
$time = $_GET['time'];

// Sử dụng truy vấn UPDATE để cập nhật is_online và time
$sql = "UPDATE friend_ship SET is_friend = '1', time = '$time' WHERE (sender_id = '$sender_id' OR receiver_id = '$sender_id') AND  (receiver_id = '$receiver_id' OR sender_id = '$receiver_id')";

if ($conn->query($sql) === TRUE) {
    // Nếu truy vấn UPDATE thành công, thực hiện truy vấn SELECT để lấy thông tin người dùng
    $selectSql = "SELECT * FROM users WHERE user_id = " . ($user_id == $sender_id ? $receiver_id : $sender_id);
    $result = $conn->query($selectSql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_object(); // Fetch the result as an object

        $response = array(
            'status' => true,
            'message' => 'Record updated successfully',
            'user' => $user
        );
        echo json_encode($response);
    } else {
        $response = array(
            'status' => false,
            'message' => 'Error fetching user information'
        );
        echo json_encode($response);
    }
} else {
    $response = array(
        'status' => false,
        'message' => 'Error updating record: ' . $conn->error
    );
    echo json_encode($response);
}

$conn->close();
?>
