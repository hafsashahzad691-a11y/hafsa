<?php
include 'includes/db.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle Add Task
if(isset($_POST['add_task'])) {
    $task_name = mysqli_real_escape_string($conn, $_POST['task_name']);
    $assigned_to = mysqli_real_escape_string($conn, $_POST['assigned_to']);
    $deadline = mysqli_real_escape_string($conn, $_POST['deadline']);
    $progress = mysqli_real_escape_string($conn, $_POST['progress']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $event_id = mysqli_real_escape_string($conn, $_POST['event_id']);
    
    $query = "INSERT INTO tasks (task_name, assigned_to, deadline, progress, status, event_id) 
              VALUES ('$task_name', '$assigned_to', '$deadline', '$progress', '$status', '$event_id')";
    
    if(mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Task added successfully!";
    } else {
        $_SESSION['error'] = "Failed to add task!";
    }
    header("Location: tasks.php");
    exit();
}

// Handle Update Task
if(isset($_POST['update_task'])) {
    $id = $_POST['task_id'];
    $task_name = mysqli_real_escape_string($conn, $_POST['task_name']);
    $assigned_to = mysqli_real_escape_string($conn, $_POST['assigned_to']);
    $deadline = mysqli_real_escape_string($conn, $_POST['deadline']);
    $progress = mysqli_real_escape_string($conn, $_POST['progress']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $event_id = mysqli_real_escape_string($conn, $_POST['event_id']);
    
    $query = "UPDATE tasks SET task_name='$task_name', assigned_to='$assigned_to', 
              deadline='$deadline', progress='$progress', status='$status', event_id='$event_id' WHERE id='$id'";
    
    if(mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Task updated successfully!";
    } else {
        $_SESSION['error'] = "Failed to update task!";
    }
    header("Location: tasks.php");
    exit();
}

// Handle Delete Task
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM tasks WHERE id='$id'");
    $_SESSION['success'] = "Task deleted successfully!";
    header("Location: tasks.php");
    exit();
}

// Fetch all tasks
$tasks = mysqli_query($conn, "SELECT t.*, u.name as assigned_to_name, e.event_name 
                              FROM tasks t 
                              LEFT JOIN users u ON t.assigned_to = u.id 
                              LEFT JOIN events e ON t.event_id = e.id 
                              ORDER BY t.deadline ASC");

$users = mysqli_query($conn, "SELECT id, name FROM users");
$events = mysqli_query($conn, "SELECT id, event_name FROM events");

include 'includes/header.php';
?>

<style>
    /* Page Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }
    
    .page-header h2 {
        font-size: 1.5rem;
        color: #1e293b;
        margin: 0;
    }
    
    .page-header h2 i {
        color: #667eea;
        margin-right: 10px;
    }
    
    /* Stats Row */
    .stats-row {
        display: flex;
        gap: 20px;
        margin-bottom: 25px;
    }
    
    .stat-box {
        background: white;
        border-radius: 16px;
        padding: 20px;
        flex: 1;
        display: flex;
        align-items: center;
        gap: 15px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    
    .stat-box-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .stat-box-icon.pending { background: #fef3c7; }
    .stat-box-icon.progress { background: #dbeafe; }
    .stat-box-icon.completed { background: #d1fae5; }
    
    .stat-box-icon.pending i { color: #f59e0b; }
    .stat-box-icon.progress i { color: #3b82f6; }
    .stat-box-icon.completed i { color: #10b981; }
    
    .stat-box-icon i {
        font-size: 1.5rem;
    }
    
    .stat-box-info h3 {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
        color: #1e293b;
    }
    
    .stat-box-info p {
        font-size: 0.8rem;
        color: #94a3b8;
        margin: 0;
    }
    
    /* Search Bar */
    .search-bar {
        background: white;
        border-radius: 12px;
        padding: 8px 15px;
        display: flex;
        align-items: center;
        gap: 10px;
        border: 1px solid #e2e8f0;
        width: 300px;
    }
    
    .search-bar i {
        color: #94a3b8;
    }
    
    .search-bar input {
        border: none;
        outline: none;
        width: 100%;
        font-size: 0.9rem;
    }
    
    /* Tasks Grid */
    .tasks-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .task-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        transition: all 0.3s;
    }
    
    .task-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .task-header {
        padding: 20px;
    }
    
    .task-header.pending { background: linear-gradient(135deg, #3a29d8, #3424c4); }
    .task-header.in_progress { background: linear-gradient(135deg, #3b82f6, #2563eb); }
    .task-header.completed { background: linear-gradient(135deg, #10b981, #059669); }
    
    .task-header h3 {
        font-size: 1rem;
        margin: 0;
        color: white;
        font-weight: 600;
    }
    
    .task-header .event-name {
        display: inline-block;
        background: rgba(255,255,255,0.2);
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.7rem;
        margin-top: 8px;
        color: white;
    }
    
    .task-body {
        padding: 20px;
    }
    
    .task-info {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    .info-row {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 0;
        border-bottom: 1px solid #f1f5f9;
    }
    
    .info-row i {
        width: 25px;
        color: #667eea;
        font-size: 0.9rem;
    }
    
    .info-row span:last-child {
        color: #475569;
        font-size: 0.85rem;
    }
    
    .assigned-to {
        font-weight: 600;
        color: #667eea;
    }
    
    /* Progress Bar */
    .progress-section {
        margin: 15px 0;
    }
    
    .progress-label {
        display: flex;
        justify-content: space-between;
        font-size: 0.7rem;
        margin-bottom: 5px;
        color: #94a3b8;
    }
    
    .progress-bar-custom {
        height: 8px;
        background: #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
    }
    
    .progress-fill {
        height: 100%;
        border-radius: 10px;
        transition: width 0.5s;
    }
    
    .progress-fill.pending { background: #f59e0b; }
    .progress-fill.in_progress { background: #3b82f6; }
    .progress-fill.completed { background: #10b981; }
    
    /* Status Badge */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
    }
    
    .status-badge.pending { background: #fef3c7; color: #f59e0b; }
    .status-badge.in_progress { background: #dbeafe; color: #3b82f6; }
    .status-badge.completed { background: #d1fae5; color: #10b981; }
    
    /* Task Actions */
    .task-actions {
        display: flex;
        gap: 10px;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #f1f5f9;
    }
    
    .btn-edit {
        flex: 1;
        background: #eef2ff;
        border: none;
        padding: 8px;
        border-radius: 8px;
        color: #667eea;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .btn-edit:hover {
        background: #667eea;
        color: white;
    }
    
    .btn-delete {
        flex: 1;
        background: #fee2e2;
        border: none;
        padding: 8px;
        border-radius: 8px;
        color: #ef4444;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .btn-delete:hover {
        background: #ef4444;
        color: white;
    }
    
    /* Read Only Mode */
    .readonly-badge {
        background: #f1f5f9;
        color: #94a3b8;
        padding: 8px;
        text-align: center;
        border-radius: 8px;
        font-size: 0.8rem;
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px;
        background: white;
        border-radius: 16px;
    }
    
    .empty-state i {
        font-size: 3rem;
        color: #cbd5e1;
        margin-bottom: 15px;
    }
    
    .empty-state h4 {
        color: #475569;
        margin-bottom: 10px;
    }
    
    /* Main Content Fix */
    .main-content {
        flex: 1;
        padding: 20px;
        width: 100%;
    }
    
    @media (max-width: 768px) {
        .stats-row {
            flex-direction: column;
        }
        .search-bar {
            width: 100%;
        }
    }
</style>

<div class="d-flex" style="width:100%; margin:0; padding:0;">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="brand">
            <h3><i class="fas fa-calendar-alt"></i> Smart Event Management System</h3>
        </div>
        <nav class="nav flex-column">
            <a href="dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="events.php" class="nav-link"><i class="fas fa-calendar"></i> Events</a>
            <a href="vendors.php" class="nav-link"><i class="fas fa-truck"></i> Vendors</a>
            <a href="tasks.php" class="nav-link active"><i class="fas fa-tasks"></i> Tasks</a>
            <a href="budget.php" class="nav-link"><i class="fas fa-dollar-sign"></i> Budget</a>
            <a href="profile.php" class="nav-link"><i class="fas fa-user"></i> Profile</a>
            <a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        
        <!-- Top Bar -->
        <div class="top-navbar d-flex justify-content-between align-items-center">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search tasks..." onkeyup="searchTasks()">
            </div>
            <div>
                <img src="https://ui-avatars.com/api/?name=Admin&background=667eea&color=fff&size=35" style="border-radius: 50%;" alt="Profile">
                <span class="ms-2">Welcome, <?php echo $_SESSION['user_name']; ?></span>
            </div>
        </div>

        <!-- Page Header -->
        <div class="page-header">
            <h2><i class="fas fa-tasks"></i> Task Management</h2>
            <?php if($_SESSION['user_role'] == 'admin'): ?>
                <button class="btn-primary" style="padding: 10px 20px; border-radius: 10px;" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                    <i class="fas fa-plus"></i> Add New Task
                </button>
            <?php endif; ?>
        </div>

        <!-- Stats Row -->
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-box-icon pending"><i class="fas fa-clock"></i></div>
                <div class="stat-box-info">
                    <?php 
                    $pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM tasks WHERE status='pending'"))['c'];
                    ?>
                    <h3><?php echo $pending; ?></h3>
                    <p>Pending Tasks</p>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-box-icon progress"><i class="fas fa-spinner"></i></div>
                <div class="stat-box-info">
                    <?php 
                    $in_progress = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM tasks WHERE status='in_progress'"))['c'];
                    ?>
                    <h3><?php echo $in_progress; ?></h3>
                    <p>In Progress</p>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-box-icon completed"><i class="fas fa-check-circle"></i></div>
                <div class="stat-box-info">
                    <?php 
                    $completed = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM tasks WHERE status='completed'"))['c'];
                    ?>
                    <h3><?php echo $completed; ?></h3>
                    <p>Completed</p>
                </div>
            </div>
        </div>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <!-- Tasks Grid -->
        <div class="tasks-grid" id="tasksGrid">
            <?php 
            mysqli_data_seek($tasks, 0);
            while($task = mysqli_fetch_assoc($tasks)): 
                $header_class = $task['status'];
                $progress_class = $task['status'];
            ?>
            <div class="task-card" data-task-name="<?php echo strtolower($task['task_name']); ?>">
                <div class="task-header <?php echo $header_class; ?>">
                    <h3><?php echo htmlspecialchars($task['task_name']); ?></h3>
                    <span class="event-name">📋 <?php echo htmlspecialchars($task['event_name'] ?? 'No Event'); ?></span>
                </div>
                <div class="task-body">
                    <div class="task-info">
                        <div class="info-row">
                            <i class="fas fa-user"></i>
                            <span>Assigned to: <span class="assigned-to"><?php echo htmlspecialchars($task['assigned_to_name'] ?? 'Unassigned'); ?></span></span>
                        </div>
                        <div class="info-row">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Deadline: <?php echo date('M d, Y', strtotime($task['deadline'])); ?></span>
                        </div>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div class="progress-section">
                        <div class="progress-label">
                            <span>Progress</span>
                            <span><?php echo $task['progress']; ?>%</span>
                        </div>
                        <div class="progress-bar-custom">
                            <div class="progress-fill <?php echo $progress_class; ?>" style="width: <?php echo $task['progress']; ?>%"></div>
                        </div>
                    </div>
                    
                    <!-- Status Badge -->
                    <div style="margin-top: 10px;">
                        <span class="status-badge <?php echo $task['status']; ?>">
                            <?php if($task['status'] == 'pending'): ?>
                                ⏳ Pending
                            <?php elseif($task['status'] == 'in_progress'): ?>
                                🔄 In Progress
                            <?php else: ?>
                                ✅ Completed
                            <?php endif; ?>
                        </span>
                    </div>
                    
                    <?php if($_SESSION['user_role'] == 'admin'): ?>
                        <div class="task-actions">
                            <button class="btn-edit" data-bs-toggle="modal" data-bs-target="#editTaskModal<?php echo $task['id']; ?>">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn-delete" onclick="confirmDelete('<?php echo addslashes($task['task_name']); ?>', <?php echo $task['id']; ?>, 'tasks')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="readonly-badge">
                            <i class="fas fa-lock"></i> Read Only
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Edit Task Modal -->
            <div class="modal fade" id="editTaskModal<?php echo $task['id']; ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Task</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="" method="POST">
                            <div class="modal-body">
                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                <div class="mb-3">
                                    <label>Task Name</label>
                                    <input type="text" name="task_name" class="form-control" value="<?php echo htmlspecialchars($task['task_name']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label>Assign To</label>
                                    <select name="assigned_to" class="form-select">
                                        <option value="">Select User</option>
                                        <?php 
                                        mysqli_data_seek($users, 0);
                                        while($user = mysqli_fetch_assoc($users)): ?>
                                            <option value="<?php echo $user['id']; ?>" <?php echo $task['assigned_to'] == $user['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($user['name']); ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label>Event</label>
                                    <select name="event_id" class="form-select">
                                        <option value="">Select Event</option>
                                        <?php 
                                        mysqli_data_seek($events, 0);
                                        while($event = mysqli_fetch_assoc($events)): ?>
                                            <option value="<?php echo $event['id']; ?>" <?php echo $task['event_id'] == $event['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($event['event_name']); ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Deadline</label>
                                        <input type="date" name="deadline" class="form-control" value="<?php echo $task['deadline']; ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Progress (%)</label>
                                        <input type="range" name="progress" class="form-range" min="0" max="100" value="<?php echo $task['progress']; ?>" oninput="this.nextElementSibling.value = this.value + '%'">
                                        <output class="range-value"><?php echo $task['progress']; ?>%</output>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label>Status</label>
                                    <select name="status" class="form-select">
                                        <option value="pending" <?php echo $task['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="in_progress" <?php echo $task['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                        <option value="completed" <?php echo $task['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" name="update_task" class="btn btn-primary">Update Task</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>

            <?php if(mysqli_num_rows($tasks) == 0): ?>
                <div class="empty-state" style="grid-column: 1/-1;">
                    <i class="fas fa-tasks"></i>
                    <h4>No Tasks Found</h4>
                    <p>Click "Add New Task" to get started</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Add New Task</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Task Name</label>
                        <input type="text" name="task_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Assign To</label>
                        <select name="assigned_to" class="form-select">
                            <option value="">Select User</option>
                            <?php 
                            mysqli_data_seek($users, 0);
                            while($user = mysqli_fetch_assoc($users)): ?>
                                <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Event</label>
                        <select name="event_id" class="form-select">
                            <option value="">Select Event</option>
                            <?php 
                            mysqli_data_seek($events, 0);
                            while($event = mysqli_fetch_assoc($events)): ?>
                                <option value="<?php echo $event['id']; ?>"><?php echo htmlspecialchars($event['event_name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label>Deadline</label>
                            <input type="date" name="deadline" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>Progress (%)</label>
                            <input type="range" name="progress" class="form-range" min="0" max="100" value="0" oninput="this.nextElementSibling.value = this.value + '%'">
                            <output class="range-value">0%</output>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Status</label>
                        <select name="status" class="form-select">
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_task" class="btn btn-primary">Add Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function searchTasks() {
    let input = document.getElementById('searchInput');
    let filter = input.value.toLowerCase();
    let cards = document.getElementsByClassName('task-card');
    
    for(let i = 0; i < cards.length; i++) {
        let name = cards[i].getAttribute('data-task-name');
        if(name && name.includes(filter)) {
            cards[i].style.display = "";
        } else if(name) {
            cards[i].style.display = "none";
        }
    }
}
</script>

<?php include 'includes/footer.php'; ?>