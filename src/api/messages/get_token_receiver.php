<?php 
require_once('../../config/Dbhelper.php');

$messages_list_id = $_GET['messages_list_id'];
$user_id = $_GET['user_id'];

// Lấy thông tin từ bảng messages_list
$sql = "SELECT * FROM messages_list WHERE messages_list_id = '$messages_list_id'";
$result = $conn->query($sql);

if ($result) {
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Kiểm tra nếu user_id trùng với sender_id hoặc reciever_id
        if ($user_id == $row['sender_id']) {
            $target_id = $row['receiver_id'];
        } elseif ($user_id == $row['receiver_id']) {
            $target_id = $row['sender_id'];
        } else {
            $response = array(
                'status' => false, 
            );
            echo json_encode($response);
            exit;
        }

        // Lấy thông tin từ bảng user dựa trên sender_id hoặc reciever_id
        $user_sql = "SELECT user_id, token FROM users WHERE user_id = '$target_id'";
        $user_result = $conn->query($user_sql);

        if ($user_result) {
            if ($user_result->num_rows > 0) {
                $user_row = $user_result->fetch_assoc();

                $response = array(
                    'status' => true, 
                    'data' => array(
                        'user_id' => $user_row['user_id'],
                        'token' => $user_row['token']
                    )
                );
                echo json_encode($response);
            } else {
                $response = array(
                    'status' => false, 
                    'message' => 'User not found'
                );
                echo json_encode($response);
            }
        } else {
            $response = array(
                'status' => false, 
                'message' => 'Error querying user database'
            );
            echo json_encode($response);
        }
    } else {
        $response = array(
            'status' => false, 
            'message' => 'Error login'
        );
        echo json_encode($response);
    }
} else {
    $response = array(
        'status' => false, 
        'message' => 'Error querying database'
    );
    echo json_encode($response);
}

$conn->close();
