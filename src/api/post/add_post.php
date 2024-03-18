<?php
require_once('../../config/Dbhelper.php');


$user_id = $_GET['user_id'];
$content = $_GET['content'];
$content = str_replace("[NEWLINE]", "\n", $content);
$time = $_GET['time'];

// Tạo câu truy vấn INSERT cho bảng 'post'
$postSql = "INSERT INTO post (user_id, content, time) VALUES ('$user_id', '$content', '$time')";

if ($conn->query($postSql) === TRUE) {
    // Nếu INSERT thành công, lấy post_id của bài viết vừa thêm
    $post_id = $conn->insert_id;

    // Initialize response object
    $response = new stdClass();
    $response->status = true;

    // Initialize data object
    $response->data = new stdClass();

    // Truy vấn thông tin từ bảng post
    $postSql = "SELECT 
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
WHERE post.post_id = '$post_id'
GROUP BY post.post_id
ORDER BY post.time DESC";

    $postResult = $conn->query($postSql);

    if ($postResult && $row = $postResult->fetch_assoc()) {
        // Assign post data directly to the response object
        $response->data->post_id = $row['post_id'];
        $response->data->user_id = $row['user_id'];
        $response->data->content = $row['content'];
        $response->data->time = $row['time'];
        $response->data->heart_count = new stdClass(); // Nested object for heart_count
        $response->data->comment_count = $row['comment_count'];
        $response->data->is_save_post = null;
        $response->data->image = array(); // Initialize as an array
        $response->data->full_name = $row['full_name'];
        $response->data->profile_image = $row['profile_image'];

        // Lấy thông tin từ bảng image
        $imageSql = "SELECT * FROM image WHERE post_id = '{$row['post_id']}'";
        $imageResult = $conn->query($imageSql);

        if ($imageResult) {
            while ($imageRow = $imageResult->fetch_assoc()) {
                // Thêm thông tin của mỗi hình ảnh vào mảng
                $imageData = new stdClass();
                $imageData->image_id = $imageRow['image_id'];
                $imageData->post_id = $imageRow['post_id'];
                $imageData->image = $imageRow['image'];
                $response->data->image[] = $imageData;
            }
        }

        // Lấy thông tin từ bảng heart
        $heartSql = "SELECT * FROM heart WHERE post_id = '{$row['post_id']}' AND user_id = '$user_id'";
        $heartResult = $conn->query($heartSql);

        if ($heartResult) {
            // Kiểm tra xem bản ghi có tồn tại hay không
            $isHeart = ($heartResult->num_rows > 0) ? 1 : 0;

            // Tạo nested object cho heart_count
            $response->data->heart_count->heart_count = $row['heart_count'];
            $response->data->heart_count->is_heart = $isHeart;
        }

        // Lấy thông tin từ bảng save_post
        $save_postSql = "SELECT * FROM save_post WHERE post_id = '{$row['post_id']}' AND user_id = '$user_id'";
        $save_postResult = $conn->query($save_postSql);

        if ($save_postResult) {
            $isHeart = ($save_postResult->num_rows > 0) ? 1 : 0;
            $response->data->is_save_post = $isHeart;
        }
    } else {
        $response->status = false;
        $response->message = 'Không tìm thấy thông tin bài viết';
    }

    // Output the JSON response
    echo json_encode($response);
} else {
    $response = array(
        'status' => false,
        'message' => 'Lỗi khi thêm bài viết: ' . $conn->error
    );
    echo json_encode($response);
}

$conn->close();
