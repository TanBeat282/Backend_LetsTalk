<?php
require_once('../../config/Dbhelper.php');

$friend_ship_id = $_GET['friend_ship_id'];


$sql = "DELETE FROM friend_ship WHERE friend_ship_id = $friend_ship_id";

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
