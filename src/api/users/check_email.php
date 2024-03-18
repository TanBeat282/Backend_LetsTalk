<?php
require_once('../../config/Dbhelper.php');

$email = $_GET['email'];
$sql = "SELECT * FROM users WHERE email = '$email' ";
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
