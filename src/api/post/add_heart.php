<?php
require_once('../../config/Dbhelper.php');

$post_id = $_GET['post_id'];
$user_id = $_GET['user_id'];
$time = $_GET['time'];

// Kiểm tra xem post_id có tồn tại trong bảng post không
$checkPostSql = "SELECT * FROM post WHERE post_id = '$post_id'";
$postResult = $conn->query($checkPostSql);

if ($postResult->num_rows == 0) {
    // Nếu post_id không tồn tại trong bảng post, xử lý lỗi tại đây
    $response = array(
        'status' => false,
        'message' => 'post_id không tồn tại trong bảng post'
    );
    echo json_encode($response);
    exit(); // Dừng chương trình để không thực hiện thêm hoặc xóa tiếp theo
}

// Kiểm tra xem bản ghi đã tồn tại hay chưa
$checkHeartSql = "SELECT * FROM heart WHERE post_id = '$post_id' AND user_id = '$user_id'";
$result = $conn->query($checkHeartSql);

if ($result->num_rows > 0) {
    // Bản ghi đã tồn tại, do đó, xóa nó
    $deleteSql = "DELETE FROM heart WHERE post_id = '$post_id' AND user_id = '$user_id'";

    if ($conn->query($deleteSql) === TRUE) {

        // Lấy số lượng bản ghi trong bảng heart dựa trên post_id
        $countSql = "SELECT COUNT(*) AS heart_count FROM heart WHERE post_id = '$post_id'";
        $countResult = $conn->query($countSql);

        if ($countResult) {
            $countRow = $countResult->fetch_assoc();
            $heartCount = $countRow['heart_count'];
        } else {
            $heartCount = 0; // Nếu có lỗi trong câu truy vấn, đặt heartCount là 0
        }

        $response = array(
            'status' => true,
            'is_heart' => '0',
            'heart_count' => $heartCount
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
    $insertSql = "INSERT INTO heart (post_id, user_id, time) VALUES ('$post_id', '$user_id', '$time')";

    if ($conn->query($insertSql) === TRUE) {

        // Lấy số lượng bản ghi trong bảng heart dựa trên post_id
        $countSql = "SELECT COUNT(*) AS heart_count FROM heart WHERE post_id = '$post_id'";
        $countResult = $conn->query($countSql);

        if ($countResult) {
            $countRow = $countResult->fetch_assoc();
            $heartCount = $countRow['heart_count'];
        } else {
            $heartCount = 0; // Nếu có lỗi trong câu truy vấn, đặt heartCount là 0
        }

        $response = array(
            'status' => true,
            'is_heart' => '1',
            'heart_count' => $heartCount
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
