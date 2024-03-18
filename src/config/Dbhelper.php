<?php
    $conn = new mysqli('localhost', 'root', '', 'id21496189_letstalks');

    if ($conn->connect_error) {
        echo("Kết nối thất bại: " + $conn->connect_error);
    }
?>
