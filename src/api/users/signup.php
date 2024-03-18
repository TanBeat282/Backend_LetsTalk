<?php
require_once('../../config/Dbhelper.php');

$email = $_GET['email'];
$password = $_GET['password'];
$decodedPass = base64_decode($password);
$full_name = $_GET['full_name'];

// Thêm các giá trị mặc định cho sex, profile_image, address trong cùng một truy vấn INSERT
$postSql = "INSERT INTO users (email, password, full_name, sex, profile_image, address, dob) 
            VALUES ('$email', '$decodedPass', '$full_name', -1, 'https://firebasestorage.googleapis.com/v0/b/letstalk-3d1c5.appspot.com/o/users%2Fimage_default%2Favatar.jpg?alt=media&token=d37017b1-43f4-4cc8-9853-00986804ab57', ' ', NOW())";

if ($conn->query($postSql) === TRUE) {
    $post_id = $conn->insert_id;

    $response = array(
        'status' => true,
        'data' => $post_id
    );
    echo json_encode($response);
} else {
    $response = array(
        'status' => false,
        'message' => 'Loi dang ki: ' . $conn->error
    );
    echo json_encode($response);
}

$conn->close();
?>
