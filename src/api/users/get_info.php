<?php 
require_once('../../config/Dbhelper.php');

$user_id = $_GET['user_id'];
$sql = "SELECT * FROM users WHERE user_id = '$user_id'";
$result = $conn->query($sql);

if ($result) {
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $response = array(
            'status' => true, 
            'data' => $row
        );
        echo json_encode($response);
    } else {
        $response = array(
            'status' => false, 
            'message' => 'error login'
        );
        echo json_encode($response);
    }
} else {
    $response = array(
        'status' => false, 
        'message' => 'error query database'
    );
    echo json_encode($response);
}

$conn->close();
