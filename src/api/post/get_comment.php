<?php
require_once('../../config/Dbhelper.php');

$post_id = $_GET['post_id'];
$is_latest = $_GET['is_latest'];

// Lấy thông tin comment từ bảng comment theo post_id và sắp xếp theo thời gian
if($is_latest ==1){
    $sql_comment = "SELECT c.*, u.full_name, u.profile_image FROM comment c
    JOIN users u ON c.user_id = u.user_id
    WHERE c.post_id = '$post_id'
    ORDER BY c.time DESC"; 
}else{
    $sql_comment = "SELECT c.*, u.full_name, u.profile_image FROM comment c
                JOIN users u ON c.user_id = u.user_id
                WHERE c.post_id = '$post_id'
                ORDER BY c.time ASC"; 
}
$result_comment = $conn->query($sql_comment);

if ($result_comment) {
    $response = array();

    if ($result_comment->num_rows > 0) {
        while ($comment_row = $result_comment->fetch_assoc()) {
            $comment = array(
                'comment_id' => $comment_row['comment_id'],
                'user_id' => $comment_row['user_id'],
                'post_id' => $comment_row['post_id'],
                'content' => $comment_row['content'],
                'time' => $comment_row['time'],
                'full_name' => $comment_row['full_name'],
                'profile_image' => $comment_row['profile_image']
            );

            $response[] = $comment;
        }
    }

    echo json_encode(array('status' => true, 'data' => $response));
} else {
    echo json_encode(array('status' => false, 'message' => 'Error querying comment data'));
}

$conn->close();
?>
