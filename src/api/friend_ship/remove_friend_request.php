<?php
require_once('../../config/Dbhelper.php');

$sender_id = $_GET['sender_id'];
$receiver_id = $_GET['receiver_id'];

$sql = "DELETE FROM friend_ship WHERE (sender_id = '$sender_id' OR receiver_id = '$sender_id') AND  (receiver_id = '$receiver_id' OR sender_id = '$receiver_id')";

if ($conn->query($sql) === TRUE) {
    $response = array(
        'status' => true,
        'message' => 'Record deleted successfully'
    );
    echo json_encode($response);
} else {
    $response = array(
        'status' => false,
        'message' => 'Error deleting record: ' . $conn->error
    );
    echo json_encode($response);
}

$conn->close();
