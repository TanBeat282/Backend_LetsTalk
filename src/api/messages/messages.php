<?php
require_once('../../config/Dbhelper.php');


$messages_list_id = $_GET['messages_list_id'];
$page = isset($_GET['page']) ? $_GET['page'] : 1; // Trang hiện tại, mặc định là 1
$pageSize = 10; // Kích thước trang

$start = ($page - 1) * $pageSize; // Vị trí bắt đầu lấy dữ liệu

$sql = "SELECT * FROM messages WHERE messages_list_id = '$messages_list_id' ORDER BY time DESC LIMIT $start, $pageSize";
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
