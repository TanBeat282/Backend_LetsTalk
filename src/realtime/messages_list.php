<?php

namespace messages_list;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use \PDOException;

require dirname(__DIR__) . '/vendor/autoload.php';
require '../src/config/Dbhelper.php';

class messages_list implements MessageComponentInterface
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

        } catch (PDOException $e) {
            // Xử lý lỗi nếu cần
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        echo "Disconected\n" . $conn->resourceId . "\n";

    }


    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
