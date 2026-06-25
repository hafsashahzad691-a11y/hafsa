<?php
include 'includes/db.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle Add Vendor
if(isset($_POST['add_vendor'])) {
    $vendor_name = mysqli_real_escape_string($conn, $_POST['vendor_name']);
    $service_type = mysqli_real_escape_string($conn, $_POST['service_type']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $charges = mysqli_real_escape_string($conn, $_POST['charges']);
    
    $query = "INSERT INTO vendors (vendor_name, service_type, contact, charges) 
              VALUES ('$vendor_name', '$service_type', '$contact', '$charges')";
    
    if(mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Vendor added successfully!";
    } else {
        $_SESSION['error'] = "Failed to add vendor!";
    }
    header("Location: vendors.php");
    exit();
}

// Handle Update Vendor
if(isset($_POST['update_vendor'])) {
    $id = $_POST['vendor_id'];
    $vendor_name = mysqli_real_escape_string($conn, $_POST['vendor_name']);
    $service_type = mysqli_real_escape_string($conn, $_POST['service_type']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $charges = mysqli_real_escape_string($conn, $_POST['charges']);
    
    $query = "UPDATE vendors SET vendor_name='$vendor_name', service_type='$service_type', 
              contact='$contact', charges='$charges' WHERE id='$id'";
    
    if(mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Vendor updated successfully!";
    } else {
        $_SESSION['error'] = "Failed to update vendor!";
    }
    header("Location: vendors.php");
    exit();
}

// Handle Delete Vendor
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM vendors WHERE id='$id'");
    $_SESSION['success'] = "Vendor deleted successfully!";
    header("Location: vendors.php");
    exit();
}

// Fetch all vendors
$vendors = mysqli_query($conn, "SELECT * FROM vendors ORDER BY id DESC");

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
    
    .stat-box-icon.purple { background: #eef2ff; }
    .stat-box-icon.green { background: #d1fae5; }
    .stat-box-icon.orange { background: #fef3c7; }
    
    .stat-box-icon i {
        font-size: 1.5rem;
    }
    
    .stat-box-icon.purple i { color: #667eea; }
    .stat-box-icon.green i { color: #10b981; }
    .stat-box-icon.orange i { color: #f59e0b; }
    
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
    
    /* Vendors Grid */
    .vendors-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .vendor-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        transition: all 0.3s;
    }
    
    .vendor-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .vendor-header {
        background: linear-gradient(135deg, #667eea, #764ba2);
        padding: 20px;
        color: white;
    }
    
    .vendor-header h3 {
        font-size: 1.1rem;
        margin: 0;
        font-weight: 600;
    }
    
    .vendor-header .service-badge {
        display: inline-block;
        background: rgba(255,255,255,0.2);
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.7rem;
        margin-top: 8px;
    }
    
    .vendor-body {
        padding: 20px;
    }
    
    .vendor-info {
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
    
    .charge-amount {
        font-weight: 700;
        color: #667eea;
        font-size: 1rem;
    }
    
    .vendor-actions {
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
            <a href="vendors.php" class="nav-link active"><i class="fas fa-truck"></i> Vendors</a>
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
                <input type="text" id="searchInput" placeholder="Search vendors..." onkeyup="searchVendors()">
            </div>
            <div>
                <img src="https://ui-avatars.com/api/?name=Admin&background=667eea&color=fff&size=35" style="border-radius: 50%;" alt="Profile">
                <span class="ms-2">Welcome, <?php echo $_SESSION['user_name']; ?></span>
            </div>
        </div>

        <!-- Page Header -->
        <div class="page-header">
            <h2><i class="fas fa-truck"></i> Vendor Management</h2>
            <?php if($_SESSION['user_role'] == 'admin'): ?>
                <button class="btn-primary" style="padding: 10px 20px; border-radius: 10px;" data-bs-toggle="modal" data-bs-target="#addVendorModal">
                    <i class="fas fa-plus"></i> Add New Vendor
                </button>
            <?php endif; ?>
        </div>

        <!-- Stats Row -->
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-box-icon purple"><i class="fas fa-truck"></i></div>
                <div class="stat-box-info">
                    <h3><?php echo mysqli_num_rows($vendors); ?></h3>
                    <p>Total Vendors</p>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-box-icon green"><i class="fas fa-dollar-sign"></i></div>
                <div class="stat-box-info">
                    <?php 
                    $total_charges = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(charges) as total FROM vendors"))['total'];
                    ?>
                    <h3>$<?php echo number_format($total_charges ?? 0, 0); ?></h3>
                    <p>Total Charges</p>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-box-icon orange"><i class="fas fa-clock"></i></div>
                <div class="stat-box-info">
                    <h3><?php echo mysqli_num_rows($vendors); ?></h3>
                    <p>Active Vendors</p>
                </div>
            </div>
        </div>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <!-- Vendors Grid -->
        <div class="vendors-grid" id="vendorsGrid">
            <?php 
            mysqli_data_seek($vendors, 0);
            while($vendor = mysqli_fetch_assoc($vendors)): 
            ?>
            <div class="vendor-card" data-vendor-name="<?php echo strtolower($vendor['vendor_name']); ?>">
                <div class="vendor-header">
                    <h3><?php echo htmlspecialchars($vendor['vendor_name']); ?></h3>
                    <span class="service-badge"><?php echo htmlspecialchars($vendor['service_type']); ?></span>
                </div>
                <div class="vendor-body">
                    <div class="vendor-info">
                        <div class="info-row">
                            <i class="fas fa-phone"></i>
                            <span><?php echo htmlspecialchars($vendor['contact']); ?></span>
                        </div>
                        <div class="info-row">
                            <i class="fas fa-dollar-sign"></i>
                            <span class="charge-amount">$<?php echo number_format($vendor['charges'], 2); ?></span>
                        </div>
                    </div>
                    
                    <?php if($_SESSION['user_role'] == 'admin'): ?>
                        <div class="vendor-actions">
                            <button class="btn-edit" data-bs-toggle="modal" data-bs-target="#editVendorModal<?php echo $vendor['id']; ?>">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn-delete" onclick="confirmDelete('<?php echo addslashes($vendor['vendor_name']); ?>', <?php echo $vendor['id']; ?>, 'vendors')">
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

            <!-- Edit Vendor Modal -->
            <div class="modal fade" id="editVendorModal<?php echo $vendor['id']; ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Vendor</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="" method="POST">
                            <div class="modal-body">
                                <input type="hidden" name="vendor_id" value="<?php echo $vendor['id']; ?>">
                                <div class="mb-3">
                                    <label>Vendor Name</label>
                                    <input type="text" name="vendor_name" class="form-control" value="<?php echo htmlspecialchars($vendor['vendor_name']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label>Service Type</label>
                                    <input type="text" name="service_type" class="form-control" value="<?php echo htmlspecialchars($vendor['service_type']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label>Contact Number</label>
                                    <input type="text" name="contact" class="form-control" value="<?php echo htmlspecialchars($vendor['contact']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label>Charges ($)</label>
                                    <input type="number" step="0.01" name="charges" class="form-control" value="<?php echo $vendor['charges']; ?>" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" name="update_vendor" class="btn btn-primary">Update Vendor</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>

            <?php if(mysqli_num_rows($vendors) == 0): ?>
                <div class="empty-state" style="grid-column: 1/-1;">
                    <i class="fas fa-truck"></i>
                    <h4>No Vendors Found</h4>
                    <p>Click "Add New Vendor" to get started</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Vendor Modal -->
<div class="modal fade" id="addVendorModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Add New Vendor</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Vendor Name</label>
                        <input type="text" name="vendor_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Service Type</label>
                        <input type="text" name="service_type" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Contact Number</label>
                        <input type="text" name="contact" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Charges ($)</label>
                        <input type="number" step="0.01" name="charges" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_vendor" class="btn btn-primary">Add Vendor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function searchVendors() {
    let input = document.getElementById('searchInput');
    let filter = input.value.toLowerCase();
    let cards = document.getElementsByClassName('vendor-card');
    
    for(let i = 0; i < cards.length; i++) {
        let name = cards[i].getAttribute('data-vendor-name');
        if(name && name.includes(filter)) {
            cards[i].style.display = "";
        } else if(name) {
            cards[i].style.display = "none";
        }
    }
}
</script>

<?php include 'includes/footer.php'; ?>