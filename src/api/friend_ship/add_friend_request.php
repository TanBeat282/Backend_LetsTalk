<?php
require_once('../../config/Dbhelper.php');

class Friend_Ship
{
    public function insertFriendShip()
    {
        global $conn;

        $sender_id = $_GET['sender_id'];
        $receiver_id = $_GET['receiver_id'];
        $time = $_GET['time'];

        $sql = "INSERT INTO friend_ship (sender_id, receiver_id, time, is_friend) 
                VALUES ('$sender_id', '$receiver_id', '$time' , 0)";

        if ($conn->query($sql) === TRUE) {
            $response = array(
                'status' => true,
                'message' => 'Record inserted successfully'
            );

            echo json_encode($response);

            $this->getData($sender_id, $receiver_id, $time,0);
        } else {
            $response = array(
                'status' => false,
                'message' => 'Error inserting record: ' . $conn->error
            );
            echo json_encode($response);
        }
    }

    public function getData($sender_id, $receiver_id, $time, $is_friend)
    {
        global $conn;

        $query_socket = "SELECT token FROM socket WHERE user_id = ?";
        $stmt = $conn->prepare($query_socket);
        $stmt->bind_param('s', $receiver_id);
        $stmt->execute();
        $result_socket = $stmt->get_result();
        $socketRow = $result_socket->fetch_assoc();
        $token = $socketRow['token'];

        $query_users = "SELECT full_name, profile_image, is_online FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($query_users);
        $stmt->bind_param('s', $sender_id);
        $stmt->execute();
        $result_users = $stmt->get_result();
        $row_users = $result_users->fetch_assoc();

        $full_name = $row_users['full_name'];
        $profile_image = $row_users['profile_image'];

        $friend_ship = [
            'sender_id' => $sender_id,
            'receiver_id' => $receiver_id,
            'time' => $time,
            'is_friend' => $is_friend,
        ];

        $json_friend_ship = json_encode($friend_ship);

        $data = [
            'friend_ship' => $json_friend_ship,
            'sender_id' => $sender_id,
            'full_name' => $full_name,
            'profile_image' => $profile_image,
        ];

        $response = array(
            // friend_ship = 1 messages = 0; post =2;
            'type_notificaiton' => 1,
            'data' => $data
        );

        $this->sendFirebaseNotification($token, $full_name, $response);
    }

    public function sendFirebaseNotification($token, $body, $data)
    {
        $url = 'https://fcm.googleapis.com/fcm/send';

        $fields = [
            'to' => $token,
            'notification' => [
                'title' => 'Lets Talk',
                'body' => $body . ' vừa gửi lời mời kết bạn'
            ],
            'data' => $data
        ];

        $headers = [
            'Authorization: key=AAAA3OwGdzc:APA91bGc6RU6SsX3oWQoeKxMo0EA9OTjjjnJi7ZAw7qvWyLgF1RVM37giT2G5FiP7zKunKCHB8iOZXshus8v4-9QWXMkVIKIts5uPKQW-Av5ko-R0-Z6OFjR70N8NdjHc2WXEAYNbvXU', // Replace 'YOUR_SERVER_KEY' with your server key from Firebase Console
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, true);
    }
}

// Usage:
$yourClassInstance = new Friend_Ship();
$yourClassInstance->insertFriendShip();
