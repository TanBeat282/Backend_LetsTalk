<?php

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

require dirname(__DIR__) . '/vendor/autoload.php';
require '../src/config/Dbhelper.php';

class Chat implements MessageComponentInterface
{
    private $clients;
    private $db;

    public function __construct()
    {
        $this->clients = array();
        global $conn;
        $this->db = $conn;
    }

    public function onOpen(ConnectionInterface $conn)
    {

        // Lấy thông tin user_id, token và recoureID 
        $query = $conn->httpRequest->getUri()->getQuery();
        parse_str($query, $queryParams);
        $token = isset($queryParams['token']) ? $queryParams['token'] : null;
        $user_id = isset($queryParams['user_id']) ? $queryParams['user_id'] : null;
        $recoureID = $conn->resourceId;

        echo "Connected " . $conn->resourceId . "\n";


        // Kiểm tra xem đã tồn tại một socket với user_id tương tự hay chưa
        $queryCheck = "SELECT * FROM socket WHERE user_id = ?";
        $stmtCheck = $this->db->prepare($queryCheck);
        $stmtCheck->bind_param('s', $user_id);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();

        if ($resultCheck->num_rows > 0) {
            // Nếu đã tồn tại, thực hiện truy vấn cập nhật
            $queryUpdate = "UPDATE socket SET token = ?, recoureID = ? WHERE user_id = ?";
            $stmtUpdate = $this->db->prepare($queryUpdate);
            $stmtUpdate->bind_param('sss', $token, $recoureID, $user_id);
            $stmtUpdate->execute();
        } else {
            // Nếu chưa tồn tại, thực hiện truy vấn chèn mới
            $queryInsert = "INSERT INTO socket (user_id, token, recoureID) VALUES (?, ?, ?)";
            $stmtInsert = $this->db->prepare($queryInsert);
            $stmtInsert->bind_param('sss', $user_id, $token, $recoureID);
            $stmtInsert->execute();
        }


        $zeroValue = 1;
        $query = "UPDATE users SET is_online = ? WHERE user_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('ii', $zeroValue, $user_id);
        $stmt->execute();

        $this->clients[] = $conn;
    }


    public function onMessage(ConnectionInterface $from, $msg)
    {
        // Xử lý tin nhắn nhận được từ Android Studio
        $data = json_decode($msg);

        $messagesId = $data->messages_id;
        $senderId = $data->sender_id;
        $content = $data->content;
        $time = $data->time;
        $typeMessage = $data->type_message;
        $messagesListId = $data->messages_list_id;

        try {

            $temp_receiver_id = 0;

            // Truy vấn để lấy sender_id và receiver_id từ messages_list
            $query_messages_list = "SELECT sender_id, receiver_id FROM messages_list WHERE messages_list_id = ?";
            $stmt = $this->db->prepare($query_messages_list);
            $stmt->bind_param('i', $messagesListId);
            $stmt->execute();
            $result_messages_list = $stmt->get_result();
            $socket_messages_list = $result_messages_list->fetch_assoc();

            // Lấy giá trị sender_id và receiver_id từ kết quả truy vấn
            $sender_id = $socket_messages_list['sender_id'];
            $receiver_id = $socket_messages_list['receiver_id'];

            if ($senderId == $sender_id) {
                $temp_receiver_id = $receiver_id;
            } else {
                $temp_receiver_id = $sender_id;
            }

            $is_seen = 0;
            if ($temp_receiver_id != 0) {
                $query = "UPDATE messages_list SET sender_id = ?, receiver_id = ?, last_content = ?, type_message = ?, time = ?, is_seen = ? WHERE messages_list_id = ?";

                // Chuẩn bị và bind các tham số
                $stmt = $this->db->prepare($query);
                $stmt->bind_param('iisisii', $senderId, $temp_receiver_id, $content, $typeMessage, $time, $is_seen, $messagesListId);
                $stmt->execute();
            }

            // Chuẩn bị truy vấn SQL chung
            $query = ($messagesId == 0)
                ? "INSERT INTO messages (messages_list_id, sender_id, content, time, type_message) VALUES (?, ?, ?, ?, ?)"
                : "UPDATE messages SET messages_list_id = ?, sender_id = ?, content = ?, time = ?, type_message = ? WHERE messages_id = ?";

            // Chuẩn bị và bind các tham số
            $stmt = $this->db->prepare($query);
            if ($messagesId == 0) {
                $stmt->bind_param('iissi', $messagesListId, $senderId, $content, $time, $typeMessage);
            } else {
                $stmt->bind_param('iissii', $messagesListId, $senderId, $content, $time, $typeMessage, $messagesId);
            }

            // Thực hiện truy vấn
            $stmt->execute();

            // Chuẩn bị mảng chung
            $response = [
                'messages_id' => ($messagesId == 0) ? $this->db->insert_id : $messagesId,
                'messages_list_id' => $messagesListId,
                'sender_id' => $senderId,
                'content' => $content,
                'time' => $time,
                'type_message' => $typeMessage,
            ];

            // Truy vấn bảng messlist để xác định sender_id và receiver_id Xác định user_id để gửi thông báo
            $query_messages_list = "SELECT receiver_id FROM messages_list WHERE messages_list_id = ?";
            $stmt = $this->db->prepare($query_messages_list);
            $stmt->bind_param('s', $messagesListId);
            $stmt->execute();
            $result_query_messages_list = $stmt->get_result();
            $row_query_messages_list = $result_query_messages_list->fetch_assoc();

            $targetUserId = $row_query_messages_list['receiver_id'];

            // Truy vấn để lấy socketID
            $query_socket = "SELECT token, recoureID FROM socket WHERE user_id = ?";
            $stmt = $this->db->prepare($query_socket);
            $stmt->bind_param('s', $targetUserId);
            $stmt->execute();
            $result_socket = $stmt->get_result();
            $socketRow = $result_socket->fetch_assoc();
            $token = $socketRow['token'];
            $socketID = $socketRow['recoureID'];

            // Gửi phản hồi đến bản thân
            $from->send(json_encode($response));

            foreach ($this->clients as $client) {
                if ($client->resourceId == $socketID) {

                    $client->send(json_encode($response));

                    try {
                        // Thực hiện truy vấn để lấy thông tin full_name và profile_image từ bảng users
                        $query = "SELECT full_name, profile_image FROM users WHERE user_id = ?";
                        $stmt = $this->db->prepare($query);
                        $stmt->bind_param('s', $senderId); // Chú ý thay đổi thành thông tin cần lấy

                        // Thực hiện truy vấn
                        $stmt->execute();

                        // Lấy kết quả
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            // Lấy dòng dữ liệu đầu tiên (assumming chỉ có một dòng dữ liệu phù hợp)
                            $row = $result->fetch_assoc();

                            $full_name = $row['full_name'];
                            $profile_image = $row['profile_image'];


                            $query_messages_list = "SELECT * FROM messages_list WHERE messages_list_id = ?";
                            $stmt = $this->db->prepare($query_messages_list);
                            $stmt->bind_param('s', $messagesListId); // Chú ý thay đổi thành thông tin cần lấy

                            // Thực hiện truy vấn
                            $stmt->execute();

                            // Lấy kết quả
                            $result_messages_list = $stmt->get_result();
                            $row_messages_list = $result_messages_list->fetch_assoc();

                            $query_users = "SELECT full_name, profile_image, is_online FROM users WHERE user_id = ?";
                            $stmt = $this->db->prepare($query_users);
                            $stmt->bind_param('s', $senderId);

                            // Thực hiện truy vấn
                            $stmt->execute();

                            // Lấy kết quả
                            $result_users = $stmt->get_result();
                            $row_users = $result_users->fetch_assoc();

                            $messages_list = [
                                'messages_list_id' => $row_messages_list['messages_list_id'],
                                'sender_id' => $row_messages_list['sender_id'],
                                'receiver_id' => $row_messages_list['receiver_id'],
                                'last_content' => $row_messages_list['last_content'],
                                'type_message' => $row_messages_list['type_message'],
                                'time' => $row_messages_list['time'],
                                'is_seen' => $row_messages_list['is_seen'],
                                'receiver_avatar' => $row_users['profile_image'],
                                'receiver_name' => $row_users['full_name'],
                                'receiver_is_online' => $row_users['is_online']
                            ];

                            // Chuyển biến $messages_list thành chuỗi JSON
                            $json_messages_list = json_encode($messages_list);

                            $data = [
                                'messages_list_id' => $messagesListId,
                                'sender_id' => $senderId,
                                'profile_image' => $profile_image,
                                'messages_list' => $json_messages_list,
                            ];

                            $this->sendFirebaseNotification($token, $full_name, $content, $data);
                        }
                    } catch (PDOException $e) {
                        // Xử lý lỗi nếu cần
                        echo "Error: " . $e->getMessage() . "\n";
                    }

                    break;
                }
            }
        } catch (PDOException $e) {
            // Xử lý lỗi nếu cần
        }
    }
    public function sendFirebaseNotification($token, $title, $body, $data)
    {
        $url = 'https://fcm.googleapis.com/fcm/send';

        $fields = [
            'to' => $token,
            'notification' => [
                'title' => $title,
                'body' => $body
            ],
            'data' => $data
        ];

        $headers = [
            'Authorization: key=AAAA3OwGdzc:APA91bGc6RU6SsX3oWQoeKxMo0EA9OTjjjnJi7ZAw7qvWyLgF1RVM37giT2G5FiP7zKunKCHB8iOZXshus8v4-9QWXMkVIKIts5uPKQW-Av5ko-R0-Z6OFjR70N8NdjHc2WXEAYNbvXU', // Thay 'YOUR_SERVER_KEY' bằng server key của bạn từ Firebase Console
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


    public function onClose(ConnectionInterface $conn)
    {
        echo "Disconnected " . $conn->resourceId . "\n";
        try {
            $resourceId = $conn->resourceId;

            $query_users = "SELECT user_id FROM socket WHERE recoureID = ?";
            $stmt = $this->db->prepare($query_users);
            $stmt->bind_param('s', $resourceId);
            $stmt->execute();

            // Lấy kết quả
            $result_users = $stmt->get_result();
            $row_users = $result_users->fetch_assoc();

            $zeroValue = 0;
            $query = "UPDATE users SET is_online = ? WHERE user_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('ii', $zeroValue, $row_users['user_id']);
            $stmt->execute();
        } catch (PDOException $e) {
            // Xử lý lỗi nếu cần
            echo "Error: " . $e->getMessage() . "\n";
        }
    }



    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Chat()
        )
    ),
    8081
);

$server->run();
