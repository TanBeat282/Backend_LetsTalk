<?php
require_once('../../config/Dbhelper.php');


$user_id = $_GET['user_id'];

$sql = "
    SELECT 
        post.post_id,
        post.user_id,
        post.content,
        post.time,
        COUNT(DISTINCT heart.heart_id) AS heart_count,
        COUNT(DISTINCT comment.comment_id) AS comment_count,
        users.full_name,
        users.profile_image
    FROM post
    LEFT JOIN heart ON post.post_id = heart.post_id
    LEFT JOIN comment ON post.post_id = comment.post_id
    LEFT JOIN users ON post.user_id = users.user_id
    WHERE post.user_id = '$user_id'
    GROUP BY post.post_id
    ORDER BY post.time DESC
";

$result = $conn->query($sql);

if ($result) {
    $data = array();
    while ($row = $result->fetch_assoc()) {
        // Lấy thông tin từ bảng post
        $postData = array(
            'post_id' => $row['post_id'],
            'user_id' => $row['user_id'],
            'content' => $row['content'],
            'time' => $row['time'],
            'heart_count' => array(),  // Mảng để lưu trữ thông tin từ bảng image 
            'comment_count' => $row['comment_count'],
            'is_save_post' => null,
            'image' => array(),  // Mảng để lưu trữ thông tin từ bảng image
            'full_name' => $row['full_name'],
            'profile_image' => $row['profile_image'],
        );

        // Lấy thông tin từ bảng image
        $imageSql = "SELECT * FROM image WHERE post_id = '{$row['post_id']}'";
        $imageResult = $conn->query($imageSql);

        if ($imageResult) {
            while ($imageRow = $imageResult->fetch_assoc()) {
                $postData['image'][] = array(
                    'image_id' => $imageRow['image_id'],
                    'post_id' => $imageRow['post_id'],
                    'image' => $imageRow['image']
                );
            }
        }
        
        // Lấy thông tin từ bảng heart
        $heartSql = "SELECT * FROM heart WHERE post_id = '{$row['post_id']}' AND user_id = '$user_id'";
        $heartResult = $conn->query($heartSql);

        if ($heartResult) {
            $isHeart = ($heartResult->num_rows > 0) ? 1 : 0;
            $heartCountArray = array(
                    'heart_count' => $row['heart_count'],
                    'is_heart' => $isHeart,
            );
            $postData['heart_count'] = $heartCountArray;
        }


        // Lấy thông tin từ bảng save_post
        $save_postSql = "SELECT * FROM save_post WHERE post_id = '{$row['post_id']}' AND user_id = '$user_id'";
        $save_postResult = $conn->query($save_postSql);

        if ($save_postResult) {
            $isHeart = ($save_postResult->num_rows > 0) ? 1 : 0;
            $postData['is_save_post'] = $isHeart;
        }

        $data[] = $postData;
    }

    if (empty($data)) {
        $status = false;
        $data = "No data";
    }else{
        $status = true;
    
    }

    $response = array(
        'status' => $status,
        'data' => $data
    );
    echo json_encode($response);
} else {
    $response = array(
        'status' => false,
        'message' => 'Lỗi truy vấn cơ sở dữ liệu: ' . $conn->error
    );
    echo json_encode($response);
}

$conn->close();
