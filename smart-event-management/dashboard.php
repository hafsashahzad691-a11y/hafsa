<?php
include 'includes/db.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php"); 
    exit();
}

// Get statistics
$total_events = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM events"))['count'];
$upcoming_events = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM events WHERE event_date >= CURDATE() AND status='upcoming'"))['count'];
$total_vendors = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM vendors"))['count'];
$pending_tasks = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM tasks WHERE status != 'completed'"))['count'];

// Get budget overview
$budget_query = mysqli_query($conn, "SELECT SUM(total_budget) as total, SUM(expenses) as spent FROM budgets");
$budget_data = mysqli_fetch_assoc($budget_query);
$total_budget = $budget_data['total'] ?? 0;
$total_expenses = $budget_data['spent'] ?? 0;
$remaining_budget = $total_budget - $total_expenses;
$budget_percentage = $total_budget > 0 ? ($total_expenses / $total_budget) * 100 : 0;

// Get upcoming events
$upcoming_events_list = mysqli_query($conn, "SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date LIMIT 5");

// Get users and events for modals
$users_list = mysqli_query($conn, "SELECT id, name FROM users");
$events_list = mysqli_query($conn, "SELECT id, event_name FROM events");

// ========== NOTIFICATIONS SYSTEM ==========
$notifications = [];
$notification_count = 0;

// 1. Check pending tasks
$pending_tasks_query = mysqli_query($conn, "SELECT task_name, progress, deadline FROM tasks WHERE status != 'completed' LIMIT 5");
while($task = mysqli_fetch_assoc($pending_tasks_query)) {
    $notifications[] = [
        'icon' => 'fa-tasks',
        'icon_color' => '#4361ee',
        'title' => 'Pending Task',
        'message' => $task['task_name'] . ' - ' . $task['progress'] . '% complete',
        'link' => 'tasks.php',
        'time' => 'Due: ' . date('M d', strtotime($task['deadline']))
    ];
    $notification_count++;
}

// 2. Check low budget alerts (less than 20% remaining)
$low_budgets = mysqli_query($conn, "SELECT e.event_name, b.remaining_budget, b.total_budget 
                                     FROM budgets b 
                                     JOIN events e ON b.event_id = e.id 
                                     WHERE b.remaining_budget < (b.total_budget * 0.2)");
while($budget = mysqli_fetch_assoc($low_budgets)) {
    $percentage = ($budget['remaining_budget'] / $budget['total_budget']) * 100;
    $notifications[] = [
        'icon' => 'fa-exclamation-triangle',
        'icon_color' => '#f59e0b',
        'title' => 'Low Budget Alert',
        'message' => $budget['event_name'] . ' - Only ' . round($percentage) . '% remaining',
        'link' => 'budget.php',
        'time' => 'Action Needed'
    ];
    $notification_count++;
}

// 3. Check upcoming events this week
$upcoming_soon = mysqli_query($conn, "SELECT event_name, event_date FROM events WHERE event_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) LIMIT 3");
while($event = mysqli_fetch_assoc($upcoming_soon)) {
    $notifications[] = [
        'icon' => 'fa-calendar-day',
        'icon_color' => '#10b981',
        'title' => 'Upcoming Event',
        'message' => $event['event_name'] . ' is happening soon',
        'link' => 'events.php',
        'time' => date('M d', strtotime($event['event_date']))
    ];
    $notification_count++;
}

include 'includes/header.php';
?>

<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="brand">
            <h3><i class="fas fa-calendar-alt"></i> Smart Event Management System</h3>
        </div>
        <nav class="nav flex-column">
            <a href="dashboard.php" class="nav-link active">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="events.php" class="nav-link">
                <i class="fas fa-calendar"></i> Events
            </a>
            <a href="vendors.php" class="nav-link">
                <i class="fas fa-truck"></i> Vendors
            </a>
            <a href="tasks.php" class="nav-link">
                <i class="fas fa-tasks"></i> Tasks
            </a>
            <a href="budget.php" class="nav-link">
                <i class="fas fa-dollar-sign"></i> Budget
            </a>
            <a href="profile.php" class="nav-link">
                <i class="fas fa-user"></i> Profile
            </a>
            <a href="logout.php" class="nav-link">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        
        <div class="top-navbar d-flex justify-content-between align-items-center">
    <h4 class="mb-0">Dashboard</h4>
    <div class="d-flex align-items-center">
        <?php
        // Get profile photo from database
        $user_id = $_SESSION['user_id'];
        $photo_query = mysqli_query($conn, "SELECT profile_photo, name FROM users WHERE id='$user_id'");
        $user_photo = mysqli_fetch_assoc($photo_query);
        
        // Set photo path (show uploaded photo or auto-avatar)
        if(!empty($user_photo['profile_photo']) && file_exists("uploads/" . $user_photo['profile_photo'])) {
            $profile_pic = "uploads/" . $user_photo['profile_photo'];
        } else {
            $profile_pic = "https://ui-avatars.com/api/?name=" . urlencode($user_photo['name']) . "&background=4361ee&color=fff&size=32&rounded=true";
        }
        ?>
        <img src="<?php echo $profile_pic; ?>" alt="Profile" style="width: 32px; height: 32px; border-radius: 50%; margin-right: 10px; object-fit: cover; border: 2px solid #4361ee;">
        <span>Welcome, <?php echo $_SESSION['user_name']; ?></span>
    </div>
</div>

        <!-- Statistics Cards Grid -->
        <div class="stats-grid">
    <div class="dashboard-card">
        <div class="stat-card">
            <div class="stat-info">
                <h3 class="stat-number" data-target="<?php echo $total_events; ?>">0</h3>
                <p>Total Events</p>
            </div>
            <div class="stat-icon primary">
                <i class="fas fa-calendar-alt"></i>
            </div>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="stat-card">
            <div class="stat-info">
                <h3 class="stat-number" data-target="<?php echo $upcoming_events; ?>">0</h3>
                <p>Upcoming Events</p>
            </div>
            <div class="stat-icon success">
                <i class="fas fa-calendar-week"></i>
            </div>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="stat-card">
            <div class="stat-info">
                <h3 class="stat-number" data-target="<?php echo $total_vendors; ?>">0</h3>
                <p>Total Vendors</p>
            </div>
            <div class="stat-icon warning">
                <i class="fas fa-truck"></i>
            </div>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="stat-card">
            <div class="stat-info">
                <h3 class="stat-number" data-target="<?php echo $pending_tasks; ?>">0</h3>
                <p>Pending Tasks</p>
            </div>
            <div class="stat-icon danger">
                <i class="fas fa-tasks"></i>
            </div>
        </div>
    </div>
</div>
          
<!-- Charts Row -->
<div class="charts-row">
    <!-- Monthly Events Chart -->
    <div class="chart-card">
        <h4>Monthly Events</h4>
        <canvas id="eventsChart" style="height: 250px; width: 100%;"></canvas>
    </div>

    <!-- Task Progress -->
    <div class="chart-card">
        <h4>Task Progress</h4>
        <div class="task-progress-list">
            <div class="task-item">
                <span>Venue Booking</span>
                <div class="task-bar">
                    <div class="task-fill" style="width: 80%; background: #4361ee;"></div>
                </div>
                <span class="task-percent">80%</span>
            </div>
            <div class="task-item">
                <span>Catering</span>
                <div class="task-bar">
                    <div class="task-fill" style="width: 80%; background: #f59e0b;"></div>
                </div>
                <span class="task-percent">80%</span>
            </div>
            <div class="task-item">
                <span>Development</span>
                <div class="task-bar">
                    <div class="task-fill" style="width: 15%; background: #ef4444;"></div>
                </div>
                <span class="task-percent">15%</span>
            </div>
            <div class="task-item">
                <span>Entertainment</span>
                <div class="task-bar">
                    <div class="task-fill" style="width: 40%; background: #10b981;"></div>
                </div>
                <span class="task-percent">40%</span>
            </div>
        </div>
    </div>
</div>

<!-- Budget Overview Card -->
<div class="budget-card">
    <h4>Budget Overview</h4>
    <div class="budget-stats">
        <div class="budget-item">
            <span>Spent</span>
            <strong class="text-warning">$<?php echo number_format($total_expenses, 2); ?></strong>
        </div>
        <div class="budget-item">
            <span>Remaining</span>
            <strong class="text-success">$<?php echo number_format($remaining_budget, 2); ?></strong>
        </div>
    </div>
    <div class="progress-bar-custom">
        <div class="progress-fill" style="width: <?php echo $budget_percentage; ?>%"></div>
    </div>
    <p class="budget-percent"><?php echo round($budget_percentage, 1); ?>% of budget used</p>
</div>
        <!-- Budget and Quick Actions Row -->
        <div class="two-column-grid">
            
                 
             

            <!-- Quick Actions -->
            <div class="dashboard-card">
                <h5 class="card-title">
                    <i class="fas fa-bolt"></i> Quick Actions
                </h5>
                <?php if($_SESSION['user_role'] == 'admin'): ?>
                    <div class="quick-actions-grid">
                        <button class="btn-action" data-bs-toggle="modal" data-bs-target="#addEventModal">
                            <i class="fas fa-plus-circle"></i>
                            <span>Create Event</span>
                        </button>
                        <button class="btn-action" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                            <i class="fas fa-tasks"></i>
                            <span>Add Task</span>
                        </button>
                        <a href="vendors.php" class="btn-action">
                            <i class="fas fa-truck"></i>
                            <span>Add Vendor</span>
                        </a>
                        <a href="budget.php" class="btn-action">
                            <i class="fas fa-dollar-sign"></i>
                            <span>Set Budget</span>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="info-message">
                        <i class="fas fa-info-circle"></i>
                        <p>You are viewing as User. Contact Admin for modifications.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Upcoming Events Table -->
        <div class="dashboard-card full-width">
            <div class="card-header-custom">
                <h5 class="card-title">
                    <i class="fas fa-calendar-alt"></i> Upcoming Events
                </h5>
                <a href="events.php" class="view-all-link">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="table-responsive">
                <table class="table table-modern">
                    <thead>
                        <tr>
                            <th>Event Name</th>
                            <th>Date</th>
                            <th>Venue</th>
                            <th>Budget</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($event = mysqli_fetch_assoc($upcoming_events_list)): ?>
                        <tr>
                            <td class="event-name"><?php echo htmlspecialchars($event['event_name']); ?></td>
                            <td><i class="far fa-calendar-alt"></i> <?php echo date('M d, Y', strtotime($event['event_date'])); ?></td>
                            <td><i class="fas fa-location-dot"></i> <?php echo htmlspecialchars($event['venue']); ?></td>
                            <td><i class="fas fa-dollar-sign"></i> <?php echo number_format($event['budget'], 2); ?></td>
                            <td><span class="badge badge-primary"><?php echo ucfirst($event['status']); ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if(mysqli_num_rows($upcoming_events_list) == 0): ?>
                        <tr>
                            <td colspan="5" class="empty-state-cell">
                                <div class="empty-state-small">
                                    <i class="fas fa-calendar-times"></i>
                                    <p>No upcoming events</p>
                                    <a href="events.php" class="btn-create">Create your first event</a>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Event Modal -->
<div class="modal fade" id="addEventModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Create New Event</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="events.php" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Event Name</label>
                        <input type="text" name="event_name" class="form-control" placeholder="Enter event name" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Event Date</label>
                        <input type="date" name="event_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Venue</label>
                        <input type="text" name="venue" class="form-control" placeholder="Enter venue" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Budget ($)</label>
                            <input type="number" step="0.01" name="budget" class="form-control" placeholder="0.00" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="upcoming">Upcoming</option>
                                <option value="ongoing">Ongoing</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_event" class="btn btn-primary">Create Event</button>
                </div>
            </form>
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
            <form action="tasks.php" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Task Name</label>
                        <input type="text" name="task_name" class="form-control" placeholder="Enter task name" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Assign To</label>
                        <select name="assigned_to" class="form-select">
                            <option value="">Select User</option>
                            <?php 
                            mysqli_data_seek($users_list, 0);
                            while($user = mysqli_fetch_assoc($users_list)): ?>
                                <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Related Event</label>
                        <select name="event_id" class="form-select">
                            <option value="">Select Event</option>
                            <?php 
                            mysqli_data_seek($events_list, 0);
                            while($event = mysqli_fetch_assoc($events_list)): ?>
                                <option value="<?php echo $event['id']; ?>"><?php echo htmlspecialchars($event['event_name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Deadline</label>
                            <input type="date" name="deadline" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Progress (%)</label>
                            <input type="range" name="progress" class="form-range" min="0" max="100" value="0" 
                                   oninput="this.nextElementSibling.value = this.value + '%'">
                            <output class="range-value">0%</output>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
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

function markAllRead() {
    alert('All notifications marked as read!');
 
}
</script>

<script>
// Monthly Events Chart
const ctx = document.getElementById('eventsChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
            label: 'Events',
            data: [9.5, 12.0, 18.0, 6.0, 17.0, 26.0, 13.0, 19.0, 0, 0, 0, 0],
            borderColor: '#4361ee',
            backgroundColor: 'rgba(67, 97, 238, 0.1)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#4361ee',
            pointBorderColor: '#fff',
            pointRadius: 5,
            pointHoverRadius: 7
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: '#e2e8f0'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});
</script>
<?php include 'includes/footer.php'; ?>

