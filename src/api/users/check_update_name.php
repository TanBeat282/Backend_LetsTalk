<?php
require_once('../../config/Dbhelper.php');

$user_id = $_GET['user_id'];

// Chắc chắn rằng $time là một chuỗi thời gian hợp lệ, có thể thêm kiểm tra thêm tùy thuộc vào định dạng của bạn
$time = time();

$sql = "SELECT * FROM users WHERE user_id = $user_id";
$result = $conn->query($sql);

if ($result) {
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Lấy timestamp của update_full_name từ cơ sở dữ liệu
        $update_full_name_timestamp = strtotime($row['update_full_name']);

        if ($update_full_name_timestamp !== false) {
            // Tính khoảng cách thời gian giữa update_full_name và time
            $time_difference = ($time - $update_full_name_timestamp);

            // Tính số ngày
            $days_difference = floor($time_difference / (24 * 60 * 60));

            // Nếu số ngày là trên 30 ngày, trả về true, ngược lại trả về false
            $is_over_30_days = ($days_difference > 30);

            // Tính số ngày còn lại đến khi đủ 30 ngày
            $is_over_30_days = ($days_difference > 30);
            $remaining_days = ($is_over_30_days) ? 0 : (30 - $days_difference);

            $response = array(
                'status' => $is_over_30_days,
                'message' => ($is_over_30_days) ? "$remaining_days" : "$remaining_days"
            );
            echo json_encode($response);
        } else {
            $response = array(
                'status' => false,
                'message' => 'Lỗi xử lý thời gian trong cơ sở dữ liệu'
            );
            echo json_encode($response);
        }
    } else {
        $response = array(
            'status' => false,
            'message' => 'Không tìm thấy người dùng'
        );
        echo json_encode($response);
    }
} else {
    $response = array(
        'status' => false,
        'message' => 'Lỗi truy vấn cơ sở dữ liệu'
    );
    echo json_encode($response);
}

$conn->close();
?>

