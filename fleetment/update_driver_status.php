<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $em_id = isset($_POST['em_id']) ? (int)$_POST['em_id'] : 0;
    $status_id = isset($_POST['status_id']) ? (int)$_POST['status_id'] : 0;

    if ($em_id > 0 && in_array($status_id, [1,3])) {
        $stmt = $conn->prepare("UPDATE employee SET es_id = ? WHERE em_id = ?");
        $stmt->bind_param("ii", $status_id, $em_id);
        if ($stmt->execute()) {
            echo "OK";
        } else {
            echo "Error: " . $conn->error;
        }
        $stmt->close();
    } else {
        echo "Invalid input";
    }
} else {
    echo "Method not allowed";
}
