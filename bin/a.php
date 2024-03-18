<?php

require dirname(__DIR__) . '/vendor/autoload.php';
require '../src/config/Dbhelper.php'; // Đảm bảo đường dẫn đến file Dbhelper.php là chính xác

use chat_messages\chat_messages;
use messages_list\messages_list;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Server\IoServer;

$chatComponent = new chat_messages($conn); // Chuyển đối tượng kết nối đến constructor của chat_messages
$messagesComponent = new messages_list($conn); // Chuyển đối tượng kết nối đến constructor của messages_list

$server = IoServer::factory(
    new HttpServer(
        new WsServer($chatComponent),
        new WsServer($messagesComponent)
    ),
    8081
);

$server->run();
