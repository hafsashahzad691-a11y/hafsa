<?php
include 'includes/db.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle Add Event
if(isset($_POST['add_event'])) {
    $event_name = mysqli_real_escape_string($conn, $_POST['event_name']);
    $event_date = mysqli_real_escape_string($conn, $_POST['event_date']);
    $venue = mysqli_real_escape_string($conn, $_POST['venue']);
    $budget = mysqli_real_escape_string($conn, $_POST['budget']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $query = "INSERT INTO events (event_name, event_date, venue, budget, status, created_by) 
              VALUES ('$event_name', '$event_date', '$venue', '$budget', '$status', '{$_SESSION['user_id']}')";
    
    if(mysqli_query($conn, $query)) {
        $event_id = mysqli_insert_id($conn);
        mysqli_query($conn, "INSERT INTO budgets (event_id, total_budget, expenses, remaining_budget) 
                            VALUES ('$event_id', '$budget', 0, '$budget')");
        $_SESSION['success'] = "Event added successfully!";
    } else {
        $_SESSION['error'] = "Failed to add event!";
    }
    header("Location: events.php");
    exit();
}

// Handle Update Event
if(isset($_POST['update_event'])) {
    $id = $_POST['event_id'];
    $event_name = mysqli_real_escape_string($conn, $_POST['event_name']);
    $event_date = mysqli_real_escape_string($conn, $_POST['event_date']);
    $venue = mysqli_real_escape_string($conn, $_POST['venue']);
    $budget = mysqli_real_escape_string($conn, $_POST['budget']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $query = "UPDATE events SET event_name='$event_name', event_date='$event_date', 
              venue='$venue', budget='$budget', status='$status' WHERE id='$id'";
    
    if(mysqli_query($conn, $query)) {
        mysqli_query($conn, "UPDATE budgets SET total_budget='$budget', 
                            remaining_budget=total_budget-expenses WHERE event_id='$id'");
        $_SESSION['success'] = "Event updated successfully!";
    } else {
        $_SESSION['error'] = "Failed to update event!";
    }
    header("Location: events.php");
    exit();
}

// Handle Delete Event
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM tasks WHERE event_id='$id'");
    mysqli_query($conn, "DELETE FROM budgets WHERE event_id='$id'");
    mysqli_query($conn, "DELETE FROM events WHERE id='$id'");
    $_SESSION['success'] = "Event deleted successfully!";
    header("Location: events.php");
    exit();
}

// Fetch all events
$events = mysqli_query($conn, "SELECT * FROM events ORDER BY event_date DESC");

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
        background: #eef2ff;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .stat-box-icon i {
        font-size: 1.5rem;
        color: #667eea;
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
    
    /* Events Grid */
    .events-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .event-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        transition: all 0.3s;
    }
    
    .event-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .event-header {
        background: linear-gradient(135deg, #667eea, #764ba2);
        padding: 20px;
        color: white;
    }
    
    .event-header h3 {
        font-size: 1.1rem;
        margin: 0;
        font-weight: 600;
    }
    
    .event-header .status-badge {
        display: inline-block;
        background: rgba(255,255,255,0.2);
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.7rem;
        margin-top: 8px;
    }
    
    .event-body {
        padding: 20px;
    }
    
    .event-info {
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
    
    .budget-amount {
        font-weight: 700;
        color: #667eea;
        font-size: 1rem;
    }
    
    .event-actions {
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
    
    /* FIX FOR WHITE SPACE */
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
            <a href="events.php" class="nav-link active"><i class="fas fa-calendar"></i> Events</a>
            <a href="vendors.php" class="nav-link"><i class="fas fa-truck"></i> Vendors</a>
            <a href="tasks.php" class="nav-link"><i class="fas fa-tasks"></i> Tasks</a>
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
                <input type="text" id="searchInput" placeholder="Search events..." onkeyup="searchEvents()">
            </div>
            <div>
                <img src="https://ui-avatars.com/api/?name=Admin&background=667eea&color=fff&size=35" style="border-radius: 50%;" alt="Profile">
                <span class="ms-2">Welcome, <?php echo $_SESSION['user_name']; ?></span>
            </div>
        </div>

        <!-- Page Header -->
        <div class="page-header">
            <h2><i class="fas fa-calendar-alt"></i> Event Management</h2>
            <?php if($_SESSION['user_role'] == 'admin'): ?>
                <button class="btn-primary" style="padding: 10px 20px; border-radius: 10px;" data-bs-toggle="modal" data-bs-target="#addEventModal">
                    <i class="fas fa-plus"></i> Add New Event
                </button>
            <?php endif; ?>
        </div>

        <!-- Stats Row -->
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-box-icon"><i class="fas fa-calendar-alt"></i></div>
                <div class="stat-box-info">
                    <h3><?php echo mysqli_num_rows($events); ?></h3>
                    <p>Total Events</p>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-box-icon"><i class="fas fa-calendar-week"></i></div>
                <div class="stat-box-info">
                    <?php 
                    $upcoming = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM events WHERE status='upcoming'"))['c'];
                    ?>
                    <h3><?php echo $upcoming; ?></h3>
                    <p>Upcoming Events</p>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-box-icon"><i class="fas fa-dollar-sign"></i></div>
                <div class="stat-box-info">
                    <?php 
                    $total_budget = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(budget) as total FROM events"))['total'];
                    ?>
                    <h3>$<?php echo number_format($total_budget ?? 0, 0); ?></h3>
                    <p>Total Budget</p>
                </div>
            </div>
        </div>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <!-- Events Grid -->
        <div class="events-grid" id="eventsGrid">
            <?php 
            mysqli_data_seek($events, 0);
            while($event = mysqli_fetch_assoc($events)): 
                $status_color = $event['status'] == 'upcoming' ? '🟢' : ($event['status'] == 'ongoing' ? '🟡' : '🔵');
            ?>
            <div class="event-card" data-event-name="<?php echo strtolower($event['event_name']); ?>">
                <div class="event-header">
                    <h3><?php echo htmlspecialchars($event['event_name']); ?></h3>
                    <span class="status-badge"><?php echo $status_color; ?> <?php echo ucfirst($event['status']); ?></span>
                </div>
                <div class="event-body">
                    <div class="event-info">
                        <div class="info-row">
                            <i class="fas fa-calendar"></i>
                            <span><?php echo date('M d, Y', strtotime($event['event_date'])); ?></span>
                        </div>
                        <div class="info-row">
                            <i class="fas fa-location-dot"></i>
                            <span><?php echo htmlspecialchars($event['venue']); ?></span>
                        </div>
                        <div class="info-row">
                            <i class="fas fa-dollar-sign"></i>
                            <span class="budget-amount">$<?php echo number_format($event['budget'], 2); ?></span>
                        </div>
                    </div>
                    
                    <?php if($_SESSION['user_role'] == 'admin'): ?>
                        <div class="event-actions">
                            <button class="btn-edit" data-bs-toggle="modal" data-bs-target="#editEventModal<?php echo $event['id']; ?>">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn-delete" onclick="confirmDelete('<?php echo addslashes($event['event_name']); ?>', <?php echo $event['id']; ?>, 'events')">
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

            <!-- Edit Event Modal -->
            <div class="modal fade" id="editEventModal<?php echo $event['id']; ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Event</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="" method="POST">
                            <div class="modal-body">
                                <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                <div class="mb-3">
                                    <label>Event Name</label>
                                    <input type="text" name="event_name" class="form-control" value="<?php echo htmlspecialchars($event['event_name']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label>Event Date</label>
                                    <input type="date" name="event_date" class="form-control" value="<?php echo $event['event_date']; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label>Venue</label>
                                    <input type="text" name="venue" class="form-control" value="<?php echo htmlspecialchars($event['venue']); ?>" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Budget ($)</label>
                                        <input type="number" step="0.01" name="budget" class="form-control" value="<?php echo $event['budget']; ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Status</label>
                                        <select name="status" class="form-select">
                                            <option value="upcoming" <?php echo $event['status'] == 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                                            <option value="ongoing" <?php echo $event['status'] == 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                                            <option value="completed" <?php echo $event['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" name="update_event" class="btn btn-primary">Update Event</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>

            <?php if(mysqli_num_rows($events) == 0): ?>
                <div class="empty-state" style="grid-column: 1/-1;">
                    <i class="fas fa-calendar-alt"></i>
                    <h4>No Events Found</h4>
                    <p>Click "Add New Event" to get started</p>
                </div>
            <?php endif; ?>
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
            <form action="" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Event Name</label>
                        <input type="text" name="event_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Event Date</label>
                        <input type="date" name="event_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Venue</label>
                        <input type="text" name="venue" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label>Budget ($)</label>
                            <input type="number" step="0.01" name="budget" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>Status</label>
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

<script>
function searchEvents() {
    let input = document.getElementById('searchInput');
    let filter = input.value.toLowerCase();
    let cards = document.getElementsByClassName('event-card');
    
    for(let i = 0; i < cards.length; i++) {
        let name = cards[i].getAttribute('data-event-name');
        if(name.includes(filter)) {
            cards[i].style.display = "";
        } else {
            cards[i].style.display = "none";
        }
    }
}
</script>

<?php include 'includes/footer.php'; ?>