<?php
require_once('../../config/Dbhelper.php');


$messages_list_id = $_GET['messages_list_id'];
$user_id = $_GET['user_id'];

$sql = "SELECT * FROM messages_list WHERE messages_list_id = '$messages_list_id' ";
$result = $conn->query($sql);

if ($result) {
    if ($result->num_rows > 0) {
        $data = array();
        while ($row = $result->fetch_assoc()) {
            // Nếu user_id là sender_id, lấy thông tin từ bảng users của receiver_id
            if ($row['sender_id'] == $user_id) {
                $receiver_id = $row['receiver_id'];
                // Thực hiện truy vấn để lấy avatar, name và isonline của receiver
                $user_info_sql = "SELECT full_name, profile_image, is_online FROM users WHERE user_id = '$receiver_id'";
                $user_info_result = $conn->query($user_info_sql);
                if ($user_info_result && $user_info_result->num_rows > 0) {
                    $user_info = $user_info_result->fetch_assoc();
                    // Thêm thông tin vào mảng row
                    $row['receiver_avatar'] = $user_info['profile_image'];
                    $row['receiver_name'] = $user_info['full_name'];
                    $row['receiver_is_online'] = $user_info['is_online'];
                }
            } else {
                $sender_id = $row['sender_id'];
                // Thực hiện truy vấn để lấy avatar, name và isonline của receiver
                $user_info_sql = "SELECT full_name, profile_image, is_online FROM users WHERE user_id = '$sender_id'";
                $user_info_result = $conn->query($user_info_sql);
                if ($user_info_result && $user_info_result->num_rows > 0) {
                    $user_info = $user_info_result->fetch_assoc();
                    // Thêm thông tin vào mảng row
                    $row['receiver_avatar'] = $user_info['profile_image'];
                    $row['receiver_name'] = $user_info['full_name'];
                    $row['receiver_is_online'] = $user_info['is_online'];
                }
            }
            $data[] = $row;
        }
        $response = array(
            'status' => true, // Thành công
            'data' => $data
        );
        echo json_encode($response);
    } else {
        $response = array(
            'status' => false, // Thất bại
            'message' => 'no messages list'
        );
        echo json_encode($response);
    }
} else {
    $response = array(
        'status' => false, // Thất bại
        'message' => 'error query database'
    );
    echo json_encode($response);
}

$conn->close();
