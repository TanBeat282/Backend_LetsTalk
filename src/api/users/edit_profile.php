<?php
require_once('../../config/Dbhelper.php');

$cover_avatar = isset($_GET['cover_avatar']) ? $_GET['cover_avatar'] : null;
$profile_image = isset($_GET['profile_image']) ? $_GET['profile_image'] : null;
$description = isset($_GET['description']) ? $_GET['description'] : null;
$full_name = isset($_GET['full_name']) ? $_GET['full_name'] : null;
$dob = isset($_GET['dob']) ? $_GET['dob'] : null;
$sex = isset($_GET['sex']) ? $_GET['sex'] : null;
$address = isset($_GET['address']) ? $_GET['address'] : null;
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;

// Sử dụng truy vấn UPDATE để cập nhật thông tin của người dùng
$sql = "UPDATE users SET";


// Thêm điều kiện cập nhật từng trường nếu giá trị không phải là null
if ($cover_avatar !== null) {
    $decodedCoverAvatar = base64_decode($cover_avatar);
    $sql .= " cover_avatar = '$decodedCoverAvatar',";
}

if ($profile_image !== null) {
    $decodedProFileImage = base64_decode($profile_image);
    $sql .= " profile_image = '$decodedProFileImage',";
}

if ($description !== null) {
    $description = str_replace("[NEWLINE]", "\n", $description);
    $sql .= " description = '$description',";
}

if ($full_name !== null) {
    $sql .= " full_name = '$full_name',";
}

if ($dob !== null) {
    $sql .= " dob = '$dob',";
}

if ($sex !== null && $sex !== -2) {
    $sql .= " sex = $sex,";
}

if ($address !== null) {
    $sql .= " address = '$address',";
}

// Loại bỏ dấu phẩy cuối cùng
$sql = rtrim($sql, ",");

// Thêm điều kiện WHERE
$sql .= " WHERE user_id = $user_id";

if ($conn->query($sql) === TRUE) {
    $response = array(
        'status' => true,
        'message' => 'User information updated successfully'
    );
    echo json_encode($response);
} else {
    $response = array(
        'status' => false,
        'message' => 'Error updating user information: ' . $conn->error
    );
    echo json_encode($response);
}

$conn->close();
?>
