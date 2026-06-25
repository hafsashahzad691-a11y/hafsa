<?php
include 'includes/db.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle Add/Update Expenses
if(isset($_POST['update_expenses'])) {
    $budget_id = mysqli_real_escape_string($conn, $_POST['budget_id']);
    $expenses = mysqli_real_escape_string($conn, $_POST['expenses']);
    
    $result = mysqli_query($conn, "SELECT total_budget, expenses FROM budgets WHERE id='$budget_id'");
    $row = mysqli_fetch_assoc($result);
    
    $new_expenses = $row['expenses'] + $expenses;
    $new_remaining = $row['total_budget'] - $new_expenses;
    
    $query = "UPDATE budgets SET expenses = '$new_expenses', remaining_budget = '$new_remaining' WHERE id = '$budget_id'";
    
    if(mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Expenses updated successfully!";
    } else {
        $_SESSION['error'] = "Failed to update expenses!";
    }
    header("Location: budget.php");
    exit();
}

// Handle Add Budget
if(isset($_POST['add_budget'])) {
    $event_id = mysqli_real_escape_string($conn, $_POST['event_id']);
    $total_budget = mysqli_real_escape_string($conn, $_POST['total_budget']);
    
    $check = mysqli_query($conn, "SELECT id FROM budgets WHERE event_id='$event_id'");
    if(mysqli_num_rows($check) > 0) {
        $_SESSION['error'] = "Budget already exists for this event!";
    } else {
        $query = "INSERT INTO budgets (event_id, total_budget, expenses, remaining_budget) 
                  VALUES ('$event_id', '$total_budget', 0, '$total_budget')";
        if(mysqli_query($conn, $query)) {
            $_SESSION['success'] = "Budget added successfully!";
        } else {
            $_SESSION['error'] = "Failed to add budget!";
        }
    }
    header("Location: budget.php");
    exit();
}

// Handle Delete Budget
if(isset($_GET['delete_budget'])) {
    $budget_id = $_GET['delete_budget'];
    mysqli_query($conn, "DELETE FROM budgets WHERE id = '$budget_id'");
    $_SESSION['success'] = "Budget deleted successfully!";
    header("Location: budget.php");
    exit();
}

// Fetch all budgets with event details
$budgets = mysqli_query($conn, "SELECT b.*, e.event_name FROM budgets b JOIN events e ON b.event_id = e.id ORDER BY e.event_date DESC");

// Get all events for the modal
$all_events = mysqli_query($conn, "SELECT e.id, e.event_name, 
    (SELECT COUNT(*) FROM budgets WHERE event_id = e.id) as has_budget 
    FROM events e ORDER BY e.event_name");

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
    
    /* Budget Grid */
    .budget-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .budget-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        transition: all 0.3s;
    }
    
    .budget-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .budget-header {
        background: linear-gradient(135deg, #667eea, #764ba2);
        padding: 20px;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .budget-header h3 {
        font-size: 1.1rem;
        margin: 0;
        font-weight: 600;
    }
    
    .btn-delete-budget {
        background: rgba(255,255,255,0.2);
        border: none;
        padding: 6px 12px;
        border-radius: 8px;
        color: white;
        cursor: pointer;
        font-size: 0.7rem;
        transition: all 0.3s;
    }
    
    .btn-delete-budget:hover {
        background: rgba(255,255,255,0.3);
    }
    
    .budget-body {
        padding: 20px;
    }
    
    .budget-stats {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
    }
    
    .budget-stat-item {
        text-align: center;
        flex: 1;
    }
    
    .stat-label {
        display: block;
        font-size: 0.7rem;
        color: #94a3b8;
        margin-bottom: 5px;
    }
    
    .stat-value {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
    }
    
    .stat-value.text-warning {
        color: #f59e0b;
    }
    
    .stat-value.text-success {
        color: #10b981;
    }
    
    .progress-container {
        margin-bottom: 20px;
    }
    
    .progress-label {
        display: flex;
        justify-content: space-between;
        font-size: 0.7rem;
        margin-bottom: 5px;
        color: #94a3b8;
    }
    
    .progress {
        height: 8px;
        background: #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
    }
    
    .progress-bar {
        height: 100%;
        border-radius: 10px;
        transition: width 0.5s;
    }
    
    .progress-bar.success { background: #10b981; }
    .progress-bar.warning { background: #f59e0b; }
    .progress-bar.danger { background: #ef4444; }
    
    .expense-form .input-group {
        display: flex;
        gap: 10px;
    }
    
    .expense-form .input-group-text {
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 8px 12px;
    }
    
    .expense-form .form-control {
        flex: 1;
        padding: 8px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
    }
    
    .expense-form .btn-primary {
        background: #667eea;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        color: white;
        cursor: pointer;
    }
    
    /* Empty State */
    .empty-state-full {
        grid-column: 1 / -1;
        text-align: center;
        padding: 60px;
        background: white;
        border-radius: 16px;
    }
    
    .empty-state-content i {
        font-size: 3rem;
        color: #cbd5e1;
        margin-bottom: 15px;
    }
    
    .empty-state-content h3 {
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
        .budget-stats {
            flex-direction: column;
            gap: 10px;
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
            <a href="tasks.php" class="nav-link"><i class="fas fa-tasks"></i> Tasks</a>
            <a href="budget.php" class="nav-link active"><i class="fas fa-dollar-sign"></i> Budget</a>
            <a href="profile.php" class="nav-link"><i class="fas fa-user"></i> Profile</a>
            <a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        
        <!-- Top Bar -->
        <div class="top-navbar d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Budget Management</h4>
            <div>
                <img src="https://ui-avatars.com/api/?name=Admin&background=667eea&color=fff&size=35" style="border-radius: 50%;" alt="Profile">
                <span class="ms-2">Welcome, <?php echo $_SESSION['user_name']; ?></span>
            </div>
        </div>

        <!-- Page Header -->
        <div class="page-header">
            <h2><i class="fas fa-dollar-sign"></i> Budget Management</h2>
            <?php if($_SESSION['user_role'] == 'admin'): ?>
                <button class="btn-primary" style="padding: 10px 20px; border-radius: 10px;" data-bs-toggle="modal" data-bs-target="#addBudgetModal">
                    <i class="fas fa-plus"></i> Add Budget
                </button>
            <?php endif; ?>
        </div>

        <!-- Stats Row -->
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-box-icon"><i class="fas fa-chart-line"></i></div>
                <div class="stat-box-info">
                    <?php 
                    $total_budget_all = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_budget) as total FROM budgets"))['total'];
                    ?>
                    <h3>$<?php echo number_format($total_budget_all ?? 0, 0); ?></h3>
                    <p>Total Budget</p>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-box-icon"><i class="fas fa-wallet"></i></div>
                <div class="stat-box-info">
                    <?php 
                    $total_expenses_all = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(expenses) as total FROM budgets"))['total'];
                    ?>
                    <h3>$<?php echo number_format($total_expenses_all ?? 0, 0); ?></h3>
                    <p>Total Spent</p>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-box-icon"><i class="fas fa-piggy-bank"></i></div>
                <div class="stat-box-info">
                    <?php 
                    $total_remaining_all = ($total_budget_all ?? 0) - ($total_expenses_all ?? 0);
                    ?>
                    <h3>$<?php echo number_format($total_remaining_all, 0); ?></h3>
                    <p>Total Remaining</p>
                </div>
            </div>
        </div>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <!-- Budget Grid -->
        <div class="budget-grid">
            <?php while($budget = mysqli_fetch_assoc($budgets)): 
                $percentage = ($budget['total_budget'] > 0) ? ($budget['expenses'] / $budget['total_budget']) * 100 : 0;
                $status_color = $percentage < 50 ? 'success' : ($percentage < 80 ? 'warning' : 'danger');
            ?>
            <div class="budget-card">
                <div class="budget-header">
                    <h3><?php echo htmlspecialchars($budget['event_name']); ?></h3>
                    <?php if($_SESSION['user_role'] == 'admin'): ?>
                        <button class="btn-delete-budget" onclick="confirmDeleteBudget(<?php echo $budget['id']; ?>, '<?php echo htmlspecialchars($budget['event_name']); ?>')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    <?php endif; ?>
                </div>
                <div class="budget-body">
                    <div class="budget-stats">
                        <div class="budget-stat-item">
                            <span class="stat-label">Total Budget</span>
                            <span class="stat-value">$<?php echo number_format($budget['total_budget'], 2); ?></span>
                        </div>
                        <div class="budget-stat-item">
                            <span class="stat-label">Expenses</span>
                            <span class="stat-value text-warning">$<?php echo number_format($budget['expenses'], 2); ?></span>
                        </div>
                        <div class="budget-stat-item">
                            <span class="stat-label">Remaining</span>
                            <span class="stat-value text-success">$<?php echo number_format($budget['remaining_budget'], 2); ?></span>
                        </div>
                    </div>

                    <div class="progress-container">
                        <div class="progress-label">
                            <span>Budget Usage</span>
                            <span><?php echo round($percentage, 1); ?>%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar <?php echo $status_color; ?>" style="width: <?php echo $percentage; ?>%"></div>
                        </div>
                    </div>

                    <form method="POST" class="expense-form">
                        <input type="hidden" name="budget_id" value="<?php echo $budget['id']; ?>">
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" name="expenses" class="form-control" placeholder="Enter expense amount" required>
                            <button type="submit" name="update_expenses" class="btn-primary">
                                <i class="fas fa-plus"></i> Add Expense
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endwhile; ?>
            
            <?php if(mysqli_num_rows($budgets) == 0): ?>
            <div class="empty-state-full">
                <div class="empty-state-content">
                    <i class="fas fa-chart-line"></i>
                    <h3>No Budgets Found</h3>
                    <p>Click "Add Budget" to create a budget for your events.</p>
                    <?php if($_SESSION['user_role'] == 'admin'): ?>
                        <button class="btn-primary" data-bs-toggle="modal" data-bs-target="#addBudgetModal">
                            <i class="fas fa-plus"></i> Create Your First Budget
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Budget Modal -->
<div class="modal fade" id="addBudgetModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Add Budget for Event</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Select Event</label>
                        <select name="event_id" class="form-select" required>
                            <option value="">-- Choose an event --</option>
                            <?php 
                            mysqli_data_seek($all_events, 0);
                            while($event = mysqli_fetch_assoc($all_events)): ?>
                                <option value="<?php echo $event['id']; ?>" 
                                    <?php echo $event['has_budget'] > 0 ? 'disabled' : ''; ?>
                                    style="<?php echo $event['has_budget'] > 0 ? 'color: #94a3b8;' : ''; ?>">
                                    <?php echo htmlspecialchars($event['event_name']); ?>
                                    <?php echo $event['has_budget'] > 0 ? '(Budget Already Set)' : ''; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Total Budget ($)</label>
                        <input type="number" step="0.01" name="total_budget" class="form-control" placeholder="Enter total budget amount" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_budget" class="btn btn-primary">Set Budget</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function confirmDeleteBudget(budgetId, eventName) {
    if(confirm('Delete budget for "' + eventName + '"? This cannot be undone.')) {
        window.location.href = 'budget.php?delete_budget=' + budgetId;
    }
}
</script>

<?php include 'includes/footer.php'; ?>