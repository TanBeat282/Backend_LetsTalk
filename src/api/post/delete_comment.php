<?php
require_once('../../config/Dbhelper.php');

$comment_id = $_GET['comment_id'];


$sql = "DELETE FROM comment WHERE comment_id = $comment_id";

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
