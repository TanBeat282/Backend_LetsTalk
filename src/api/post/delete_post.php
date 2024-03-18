<?php
require_once('../../config/Dbhelper.php');


$post_id = $_GET['post_id'];


$conn->begin_transaction();

try {
   
    $deleteCommentsSql = "DELETE FROM comment WHERE post_id = '$post_id'";
    $conn->query($deleteCommentsSql);

   
    $deleteHeartsSql = "DELETE FROM heart WHERE post_id = '$post_id'";
    $conn->query($deleteHeartsSql);

   
    $deleteImagesSql = "DELETE FROM image WHERE post_id = '$post_id'";
    $conn->query($deleteImagesSql);

   
    $deletePostSql = "DELETE FROM post WHERE post_id = '$post_id'";
    $conn->query($deletePostSql);

    $conn->commit();

    $response = array(
        'status' => true,
        'message' => 'Xóa bài viết thành công'
    );
    echo json_encode($response);
} catch (Exception $e) {

    $conn->rollback();

    $response = array(
        'status' => false,
        'message' => 'Lỗi khi xóa bài viết: ' . $e->getMessage()
    );
    echo json_encode($response);
}

$conn->close();
?>
