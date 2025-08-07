<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bi_id = isset($_POST['bi_id']) ? (int)$_POST['bi_id'] : 0;
    $status_id = isset($_POST['status_id']) ? (int)$_POST['status_id'] : 0;

    if ($bi_id > 0 && in_array($status_id, [1,2,3,4])) {
        $stmt = $conn->prepare("UPDATE bus_info SET status_id = ? WHERE bi_id = ?");
        $stmt->bind_param('ii', $status_id, $bi_id);
        if ($stmt->execute()) {
            echo "OK";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Invalid input";
    }
} else {
    echo "Invalid request method";
}
?>
