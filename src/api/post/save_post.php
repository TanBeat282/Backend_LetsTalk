<?php
require_once('../../config/Dbhelper.php');

$user_id = $_GET['user_id'];
$post_id = $_GET['post_id'];
$time = $_GET['time'];

// Kiểm tra xem post_id có tồn tại trong bảng post không
$checkPostSql = "SELECT * FROM post WHERE post_id = '$post_id'";
$postResult = $conn->query($checkPostSql);

if ($postResult->num_rows == 0) {
    // Nếu post_id không tồn tại trong bảng post, xử lý lỗi tại đây
    $response = array(
        'status' => false,
        'message' => 'Error'
    );
    echo json_encode($response);
    exit(); // Dừng chương trình để không thực hiện thêm hoặc xóa tiếp theo
}

// Kiểm tra xem bản ghi đã tồn tại hay chưa
$checkHeartSql = "SELECT * FROM save_post WHERE post_id = '$post_id' AND user_id = '$user_id'";
$result = $conn->query($checkHeartSql);

if ($result->num_rows > 0) {
    // Bản ghi đã tồn tại, do đó, xóa nó
    $deleteSql = "DELETE FROM save_post WHERE post_id = '$post_id' AND user_id = '$user_id'";

    if ($conn->query($deleteSql) === TRUE) {

        $response = array(
            'status' => true,
            'is_save_post' => '0',
        );
        echo json_encode($response);
    } else {
        $response = array(
            'status' => false,
            'message' => 'Lỗi khi hủy yêu thích: ' . $conn->error
        );
        echo json_encode($response);
    }
} else {
    // Bản ghi chưa tồn tại, thêm mới nó
    $insertSql = "INSERT INTO save_post (post_id, user_id, time) VALUES ('$post_id', '$user_id', '$time')";

    if ($conn->query($insertSql) === TRUE) {

        $response = array(
            'status' => true,
            'is_save_post' => '1',
        );
        echo json_encode($response);
    } else {
        $response = array(
            'status' => false,
            'message' => 'Lỗi khi thêm yêu thích: ' . $conn->error
        );
        echo json_encode($response);
    }
}

$conn->close();
