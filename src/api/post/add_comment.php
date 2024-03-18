<?php
require_once('../../config/Dbhelper.php');


$post_id = $_GET['post_id'];
$user_id = $_GET['user_id'];
$content = $_GET['content'];
// Thay thế ký tự đặc biệt bằng ký tự xuống hàng
$content = str_replace("[NEWLINE]", "\n", $content);

$time = $_GET['time'];

// Tạo câu truy vấn INSERT cho bảng 'comment'
$commentSql = "INSERT INTO comment (post_id, user_id, content, time) VALUES ('$post_id', '$user_id', '$content', '$time')";

if ($conn->query($commentSql) === TRUE) {

    $response = array(
        'status' => true,
        'message' => 'Bình luận thành công'
    );
    echo json_encode($response);
} else {
    $response = array(
        'status' => false,
        'message' => 'Lỗi khi thêm bình luận: ' . $conn->error
    );
    echo json_encode($response);
}

$conn->close();
?>
