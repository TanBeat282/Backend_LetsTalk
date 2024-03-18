<?php
require_once('../../config/Dbhelper.php');

function searchUsersByFullName($searchTerm, $loggedInUserId) {
    global $conn;

    $sql = "SELECT users.*, friend_ship.friend_ship_id, friend_ship.sender_id, friend_ship.receiver_id, friend_ship.time, friend_ship.is_friend
            FROM users
            LEFT JOIN friend_ship ON (users.user_id = friend_ship.sender_id OR users.user_id = friend_ship.receiver_id)
                AND (friend_ship.sender_id = $loggedInUserId OR friend_ship.receiver_id = $loggedInUserId)
            WHERE users.full_name LIKE '%$searchTerm%'
            AND users.user_id != $loggedInUserId";  // Exclude the logged-in user

    $result = $conn->query($sql);

    if ($result) {
        $users = array();
        while ($row = $result->fetch_assoc()) {
            // Thêm toàn bộ thông tin của người dùng vào mảng kết quả
            $user = $row;

            // Kiểm tra nếu không có thông tin từ bảng friend_ship
            if ($user['friend_ship_id'] === null) {
                $user['friend_ship_id'] = -1;
                $user['sender_id'] = -1;
                $user['receiver_id'] = -1;
                $user['time'] = '';
                $user['is_friend'] = -1;
            }

            $users[] = $user;
        }

        // Sắp xếp mảng theo điều kiện
        usort($users, function ($a, $b) use ($searchTerm) {
            $similarityComparison = similar_text($searchTerm, $b['full_name']) <=> similar_text($searchTerm, $a['full_name']);
            $isFriendComparison = $b['is_friend'] <=> $a['is_friend'];

            // Trường hợp độ giống nhau giống nhau, sắp xếp theo trạng thái is_friend giảm dần
            if ($similarityComparison === 0) {
                return $isFriendComparison;
            }

            return $similarityComparison;
        });

        return $users;
    } else {
        return false;
    }
}

// Sử dụng hàm
$searchTerm = isset($_GET['searchTerm']) ? $_GET['searchTerm'] : '';
$loggedInUserId = isset($_GET['loggedInUserId']) ? $_GET['loggedInUserId'] : '';
$users = searchUsersByFullName($searchTerm, $loggedInUserId);

if ($users !== false) {
    echo json_encode(array('status' => true, 'users' => $users));
} else {
    echo json_encode(array('status' => false, 'message' => 'Error querying database'));
}

$conn->close();
?>
