<?php
require_once('../../config/Dbhelper.php');

$sender_id = $_GET['sender_id'];
$receiver_id = $_GET['receiver_id'];

// Thực hiện câu truy vấn JOIN để lấy dữ liệu từ cả hai bảng
$sql = "SELECT 
            users.full_name, 
            users.profile_image, 
            users.cover_avatar,
            users.description,
            COALESCE(friend_ship.sender_id, -1) AS sender_id,
            COALESCE(friend_ship.receiver_id, -1) AS receiver_id,
            COALESCE(friend_ship.is_friend, -1) AS is_friend
        FROM users
        LEFT JOIN friend_ship ON (friend_ship.sender_id = '$sender_id' OR friend_ship.receiver_id = '$sender_id')
                              AND (friend_ship.receiver_id = '$receiver_id' OR friend_ship.sender_id = '$receiver_id')
        WHERE users.user_id = '$receiver_id'";

$result = $conn->query($sql);

if ($result) {
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $response = array(
            'status' => true,
            'data' => $row
        );
        echo json_encode($response);
    } else {
        $response = array(
            'status' => false,
            'message' => 'error login'
        );
        echo json_encode($response);
    }
} else {
    $response = array(
        'status' => false,
        'message' => 'error query database'
    );
    echo json_encode($response);
}

$conn->close();
?>
