<?php
include 'includes/db.php';
header('Content-Type: application/json');

if(isset($_POST['task_id']) && isset($_POST['progress'])) {
    $task_id = $_POST['task_id'];
    $progress = $_POST['progress'];
    
    $status = 'pending';
    if($progress >= 100) {
        $status = 'completed';
    } elseif($progress > 0) {
        $status = 'in_progress';
    }
    
    $query = "UPDATE tasks SET progress='$progress', status='$status' WHERE id='$task_id'";
    mysqli_query($conn, $query);
    echo json_encode(['success' => true]);
}
?>
