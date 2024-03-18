<?php
require_once('../../config/Dbhelper.php');

$user_id = $_GET['user_id'];

// Truy vấn lấy thông tin từ bảng friend_ship
$sql = "
    SELECT 
        CASE
            WHEN friend_ship.sender_id = '$user_id' AND friend_ship.is_friend = 1 THEN friend_ship.receiver_id
            WHEN friend_ship.receiver_id = '$user_id' AND friend_ship.is_friend = 1 THEN friend_ship.sender_id
            ELSE -1
        END AS friend_id
    FROM friend_ship
    WHERE friend_ship.sender_id = '$user_id' OR friend_ship.receiver_id = '$user_id'
    ORDER BY friend_ship.time DESC
";

$result = $conn->query($sql);

if ($result) {
    $data = array(); // Mảng để lưu trữ toàn bộ dữ liệu

    while ($row = $result->fetch_assoc()) {
        // Lấy friend_id từ kết quả truy vấn
        $friend_id = $row['friend_id'];

        // Nếu không có bạn bè nào hoặc friend_id = -1, có thể xử lý thông báo hoặc thực hiện hành động khác theo yêu cầu của bạn
        if ($friend_id != -1) {
            // Tiếp tục truy vấn để lấy thông tin từ bảng post
            $postSql = "
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
                WHERE post.user_id = '$friend_id'
                GROUP BY post.post_id
                ORDER BY RAND(); 
            ";

            $postResult = $conn->query($postSql);

            if ($postResult) {
                while ($postRow = $postResult->fetch_assoc()) {
                    // Lấy thông tin từ bảng post
                    $postData = array(
                        'post_id' => $postRow['post_id'],
                        'user_id' => $postRow['user_id'],
                        'content' => $postRow['content'],
                        'time' => $postRow['time'],
                        'heart_count' => array(),  // Mảng để lưu trữ thông tin từ bảng image 
                        'comment_count' => $postRow['comment_count'],
                        'is_save_post' => null,
                        'image' => array(),  // Mảng để lưu trữ thông tin từ bảng image
                        'full_name' => $postRow['full_name'],
                        'profile_image' => $postRow['profile_image'],
                    );

                    // Lấy thông tin từ bảng image
                    $imageSql = "SELECT * FROM image WHERE post_id = '{$postRow['post_id']}'";
                    $imageResult = $conn->query($imageSql);

                    if ($imageResult) {
                        while ($imageRow = $imageResult->fetch_assoc()) {
                            // Thêm thông tin của mỗi hình ảnh vào mảng
                            $postData['image'][] = array(
                                'image_id' => $imageRow['image_id'],
                                'post_id' => $imageRow['post_id'],
                                'image' => $imageRow['image']
                            );
                        }
                    }

                    // Lấy thông tin từ bảng heart
                    $heartSql = "SELECT * FROM heart WHERE post_id = '{$postRow['post_id']}' AND user_id = '$user_id'";
                    $heartResult = $conn->query($heartSql);

                    if ($heartResult) {
                        // Kiểm tra xem bản ghi có tồn tại hay không
                        $isHeart = ($heartResult->num_rows > 0) ? 1 : 0;

                        // Tạo mảng con cho heart_count
                        $heartCountArray = array(
                            'heart_count' => $postRow['heart_count'],
                            'is_heart' => $isHeart,
                        );

                        // Thêm thông tin của mỗi hình ảnh vào mảng
                        $postData['heart_count'] = $heartCountArray;
                    }


                    // Lấy thông tin từ bảng save_post
                    $save_postSql = "SELECT * FROM save_post WHERE post_id = '{$postRow['post_id']}' AND user_id = '$user_id'";
                    $save_postResult = $conn->query($save_postSql);

                    if ($save_postResult) {
                        $isHeart = ($save_postResult->num_rows > 0) ? 1 : 0;
                        $postData['is_save_post'] = $isHeart;
                    }



                    // Thêm thông tin của mỗi bài đăng vào mảng chính
                    $data[] = $postData;
                }
            }
        } else {
        }
    }
    if ($row = $result->fetch_assoc() == null) {
        $postSql = "
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
                WHERE  post.user_id = '$user_id'
                GROUP BY post.post_id
                ORDER BY RAND(); 
            ";

        $postResult = $conn->query($postSql);

        if ($postResult) {
            while ($postRow = $postResult->fetch_assoc()) {
                // Lấy thông tin từ bảng post
                $postData = array(
                    'post_id' => $postRow['post_id'],
                    'user_id' => $postRow['user_id'],
                    'content' => $postRow['content'],
                    'time' => $postRow['time'],
                    'heart_count' => array(),  // Mảng để lưu trữ thông tin từ bảng image 
                    'comment_count' => $postRow['comment_count'],
                    'is_save_post' => null,
                    'image' => array(),  // Mảng để lưu trữ thông tin từ bảng image
                    'full_name' => $postRow['full_name'],
                    'profile_image' => $postRow['profile_image'],
                );

                // Lấy thông tin từ bảng image
                $imageSql = "SELECT * FROM image WHERE post_id = '{$postRow['post_id']}'";
                $imageResult = $conn->query($imageSql);

                if ($imageResult) {
                    while ($imageRow = $imageResult->fetch_assoc()) {
                        // Thêm thông tin của mỗi hình ảnh vào mảng
                        $postData['image'][] = array(
                            'image_id' => $imageRow['image_id'],
                            'post_id' => $imageRow['post_id'],
                            'image' => $imageRow['image']
                        );
                    }
                }

                // Lấy thông tin từ bảng heart
                $heartSql = "SELECT * FROM heart WHERE post_id = '{$postRow['post_id']}' AND user_id = '$user_id'";
                $heartResult = $conn->query($heartSql);

                if ($heartResult) {
                    // Kiểm tra xem bản ghi có tồn tại hay không
                    $isHeart = ($heartResult->num_rows > 0) ? 1 : 0;

                    // Tạo mảng con cho heart_count
                    $heartCountArray = array(
                        'heart_count' => $postRow['heart_count'],
                        'is_heart' => $isHeart,
                    );

                    // Thêm thông tin của mỗi hình ảnh vào mảng
                    $postData['heart_count'] = $heartCountArray;
                }


                // Lấy thông tin từ bảng save_post
                $save_postSql = "SELECT * FROM save_post WHERE post_id = '{$postRow['post_id']}' AND user_id = '$user_id'";
                $save_postResult = $conn->query($save_postSql);

                if ($save_postResult) {
                    $isHeart = ($save_postResult->num_rows > 0) ? 1 : 0;
                    $postData['is_save_post'] = $isHeart;
                }



                // Thêm thông tin của mỗi bài đăng vào mảng chính
                $data[] = $postData;
            }
        }
    }

    if (empty($data)) {
        // Nếu không có dữ liệu, trả về thông báo "No data"
        $data = array();
    }

    $response = array(
        'status' => true,
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
