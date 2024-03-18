<?php
require_once('../../config/Dbhelper.php');


$messages_list_id = $_GET['messages_list_id'];
$content = $_GET['content'];

$sql = "
    SELECT messages.*, users.full_name, users.profile_image
    FROM messages
    JOIN users ON messages.sender_id = users.user_id
    WHERE messages.messages_list_id = '$messages_list_id'
    AND messages.content LIKE '%$content%'
    AND messages.type_message ='0'
    ORDER BY messages.time DESC
";

$result = $conn->query($sql);

if ($result) {
    if ($result->num_rows > 0) {
        $data = array();
        while ($row = $result->fetch_assoc()) {
            // Chỉ lấy những cột cần thiết từ bảng messages
            $messageData = array(
                'messages_id' => $row['messages_id'],
                'sender_id' => $row['sender_id'],
                'content' => $row['content'],
                'time' => $row['time'],
                'type_message' => $row['type_message'],
                // Thêm thông tin từ bảng users
                'full_name' => $row['full_name'],
                'profile_image' => $row['profile_image']
                // Các cột khác từ bảng messages có thể thêm vào nếu cần
            );
            $data[] = $messageData;
        }
        $response = array(
            'status' => true, // Thành công
            'data' => $data
        );
        echo json_encode($response);
    } else {
        $response = array(
            'status' => false, // Thất bại
            'message' => 'Không có tin nhắn'
        );
        echo json_encode($response);
    }
} else {
    $response = array(
        'status' => false, // Thất bại
        'message' => 'Lỗi truy vấn cơ sở dữ liệu: ' . $conn->error
    );
    echo json_encode($response);
}

$conn->close();
?>
