<?php
session_start();
include __DIR__ . '/config/db.php';

// Hapus Task
if (isset($_POST['delete_task'])) {
    $task_id = $_POST['delete_task'];

    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->bind_param('i', $task_id);
    $stmt->execute();

    header("Location: tasks.php?list_id=" . $_POST['list_id']);
    exit();
}
?>
