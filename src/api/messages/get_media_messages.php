<?php
require_once('../../config/Dbhelper.php');

$messages_list_id = $_GET['messages_list_id'];
$type_message = $_GET['type_message'];

// Modify the SQL query to handle the logic
if ($type_message == 1) {
    $sql = "SELECT * FROM messages WHERE messages_list_id = '$messages_list_id' AND (type_message = '1' OR type_message = '2') ORDER BY time DESC";
} else {
    $sql = "SELECT * FROM messages WHERE messages_list_id = '$messages_list_id' AND type_message = '$type_message' ORDER BY time DESC";
}

$result = $conn->query($sql);

if ($result) {
    if ($result->num_rows > 0) {
        $data = array();
        while ($row = $result->fetch_assoc()) {
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
            'message' => 'Không có media'
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
