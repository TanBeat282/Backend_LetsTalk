<?php
require_once('../../config/Dbhelper.php');

$user_id = $_GET['user_id'];
$sender_id = $_GET['sender_id'];
$receiver_id = $_GET['receiver_id'];

// Hàm kiểm tra sender_id và receiver_id
function checkSenderReceiver($conn, $user_id, $sender_id, $receiver_id)
{
    if ($sender_id != $receiver_id) {
        // Kiểm tra xem có dữ liệu trong messages_list không
        $checkSql = "SELECT * FROM messages_list WHERE 
            (sender_id = '$sender_id' AND receiver_id = '$receiver_id') OR 
            (sender_id = '$receiver_id' AND receiver_id = '$sender_id')";

        $checkResult = $conn->query($checkSql);

        if ($checkResult) {
            if ($checkResult->num_rows > 0) {
                // Đã có dữ liệu, lấy và trả về
                $row = $checkResult->fetch_assoc();

                // Thêm thông tin avatar, name, và is_online của receiver vào mảng row
                $receiver_id = ($row['sender_id'] == $user_id) ? $row['receiver_id'] : $row['sender_id'];
                $user_info_sql = "SELECT full_name, profile_image, is_online FROM users WHERE user_id = '$receiver_id'";
                $user_info_result = $conn->query($user_info_sql);

                if ($user_info_result && $user_info_result->num_rows > 0) {
                    $user_info = $user_info_result->fetch_assoc();
                    $row['receiver_avatar'] = $user_info['profile_image'];
                    $row['receiver_name'] = $user_info['full_name'];
                    $row['receiver_is_online'] = $user_info['is_online'];
                }

                return array(
                    'status' => true,
                    'data' => $row
                );
            } else {
                // Thêm thông tin avatar, name, và is_online của receiver vào mảng insertedData
                $user_info_sql = "SELECT full_name, profile_image, is_online FROM users WHERE user_id = '$receiver_id'";
                $user_info_result = $conn->query($user_info_sql);

                if ($user_info_result && $user_info_result->num_rows > 0) {
                    $user_info = $user_info_result->fetch_assoc();

                    $defaultData = array(
                        'messages_list_id' => -1,
                        'sender_id' => $sender_id,
                        'receiver_id' => $receiver_id,
                        'last_content' => '',
                        'type_message' => 0,
                        'time' => "",
                        'is_seen' => 0,
                        'receiver_avatar' => $user_info['profile_image'],
                        'receiver_name' => $user_info['full_name'],
                        'receiver_is_online' =>  $user_info['is_online'],
                    );
                }

                return array(
                    'status' => true,
                    'data' => $defaultData
                );
            }
        } else {
            return array(
                'status' => false,
                'message' => 'Error querying messages_list: ' . $conn->error
            );
        }
    } else {
        return array(
            'status' => false,
            'message' => 'Failed to insert messages_list'
        );
    }
}

// Hàm lấy dữ liệu bằng ID
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

// Thực hiện kiểm tra và trả về kết quả
$result = checkSenderReceiver($conn, $user_id, $sender_id, $receiver_id);

// Xuất kết quả dưới dạng JSON
echo json_encode($result);

$conn->close();
