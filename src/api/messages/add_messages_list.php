<?php
require_once('../../config/Dbhelper.php');

$user_id = $_GET['user_id'];
$sender_id = $_GET['sender_id'];
$receiver_id = $_GET['receiver_id'];

// Kiểm tra xem user_id có bằng sender_id hay receiver_id
$id_to_query = ($user_id == $sender_id) ? $receiver_id : $sender_id;

// check sender_id and receiver_id
if ($sender_id != $receiver_id) {
    $sql = "INSERT INTO messages_list (sender_id, receiver_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param('ii', $sender_id, $receiver_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // Lấy thông tin từ bảng users
            $user_info = getUserInfo($conn, $id_to_query);

            $insertedData = getInsertedData($conn, $stmt->insert_id);

            // Kết hợp thông tin từ users vào dữ liệu trả về
            $response = array(
                'status' => true,
                'message' => array_merge($insertedData, $user_info),
            );
            echo json_encode($response);
        } else {
            $response = array(
                'status' => false,
                'message' => 'Failed to insert messages_list'
            );
            echo json_encode($response);
        }

        $stmt->close();
    } else {
        $response = array(
            'status' => false,
            'message' => 'Error preparing statement: ' . $conn->error
        );
        echo json_encode($response);
    }

    $conn->close();
} else {
    $response = array(
        'status' => false,
        'message' => 'Failed to insert messages_list'
    );
    echo json_encode($response);
}

// Function to get user info by ID
function getUserInfo($conn, $user_id)
{
    $sql = "SELECT profile_image, full_name, is_online FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
    }

    return null;
}

// Function to get the inserted data by ID
function getInsertedData($conn, $insertedId)
{
    $sql = "SELECT * FROM messages_list WHERE messages_list_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param('i', $insertedId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
    }

    return null;
}

