<?php
include 'includes/db.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle Remove Photo
if(isset($_GET['remove_photo'])) {
    $query = mysqli_query($conn, "SELECT profile_photo FROM users WHERE id='$user_id'");
    $user = mysqli_fetch_assoc($query);
    if(!empty($user['profile_photo']) && file_exists("uploads/" . $user['profile_photo'])) {
        unlink("uploads/" . $user['profile_photo']);
    }
    mysqli_query($conn, "UPDATE users SET profile_photo = NULL WHERE id='$user_id'");
    $_SESSION['success'] = "Profile photo removed successfully!";
    header("Location: profile.php");
    exit();
}

$user_query = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($user_query);

// Handle Profile Update
if(isset($_POST['update_profile'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    
    $update_query = "UPDATE users SET name='$name', email='$email', phone='$phone', title='$title' WHERE id='$user_id'";
    if(mysqli_query($conn, $update_query)) {
        $_SESSION['user_name'] = $name;
        $_SESSION['success'] = "Profile updated successfully!";
        header("Location: profile.php");
        exit();
    }
}

// Handle Password Change
if(isset($_POST['change_password'])) {
    $current = md5($_POST['current_password']);
    $new = md5($_POST['new_password']);
    $confirm = md5($_POST['confirm_password']);
    
    $check = mysqli_query($conn, "SELECT id FROM users WHERE id='$user_id' AND password='$current'");
    if(mysqli_num_rows($check) == 0) {
        $_SESSION['error'] = "Current password is incorrect!";
    } elseif($_POST['new_password'] != $_POST['confirm_password']) {
        $_SESSION['error'] = "New passwords do not match!";
    } else {
        mysqli_query($conn, "UPDATE users SET password='$new' WHERE id='$user_id'");
        $_SESSION['success'] = "Password changed successfully!";
    }
    header("Location: profile.php");
    exit();
}

// Handle Photo Upload
if(isset($_POST['upload_photo'])) {
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_name = time() . "_" . basename($_FILES["profile_photo"]["name"]);
    $target_file = $target_dir . $file_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Check if image file is valid
    $check = getimagesize($_FILES["profile_photo"]["tmp_name"]);
    if($check !== false) {
        if(move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $target_file)) {
            $update_photo = "UPDATE users SET profile_photo = '$file_name' WHERE id = '$user_id'";
            mysqli_query($conn, $update_photo);
            $_SESSION['success'] = "Profile photo updated successfully!";
        } else {
            $_SESSION['error'] = "Sorry, there was an error uploading your file.";
        }
    } else {
        $_SESSION['error'] = "File is not an image.";
    }
    header("Location: profile.php");
    exit();
}

// Get current profile photo
$profile_photo = !empty($user['profile_photo']) ? "uploads/" . $user['profile_photo'] : "https://ui-avatars.com/api/?name=" . urlencode($user['name']) . "&background=667eea&color=fff&size=120";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Smart Event Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
        }

        /* Sidebar */
        .sidebar {
            height: 100vh;
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            position: fixed;
            left: 0;
            top: 0;
            width: 260px;
            transition: all 0.3s;
            z-index: 1000;
        }

        .sidebar .brand {
            padding: 25px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar .brand h3 {
            color: white;
            font-size: 1.2rem;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.7);
            padding: 12px 20px;
            margin: 5px 15px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .sidebar .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .sidebar .nav-link.active {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            padding: 25px 30px;
        }

        /* Top Navbar */
        .top-navbar {
            background: white;
            border-radius: 15px;
            padding: 15px 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Profile Grid */
        .profile-grid {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 25px;
        }

        /* Profile Card */
        .profile-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }

        .profile-card h4 {
            font-size: 1.1rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f1f5f9;
            color: #1e293b;
        }

        /* Profile Photo */
        .profile-photo {
            text-align: center;
            margin-bottom: 25px;
        }

        .profile-photo img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #667eea;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .profile-photo h3 {
            margin-top: 15px;
            font-size: 1.2rem;
            color: #1e293b;
        }

        .profile-photo p {
            color: #667eea;
            font-size: 0.85rem;
        }

        .photo-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 15px;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #475569;
            font-size: 0.85rem;
        }

        .input-group {
            display: flex;
            align-items: center;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            transition: all 0.3s;
            background: white;
        }

        .input-group:focus-within {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .input-group i {
            padding: 0 15px;
            color: #94a3b8;
        }

        .input-group input, .input-group select {
            flex: 1;
            padding: 12px 15px 12px 0;
            border: none;
            outline: none;
            font-size: 0.9rem;
            background: transparent;
        }

        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            padding: 12px 25px;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #f1f5f9;
            border: none;
            padding: 12px 25px;
            border-radius: 12px;
            color: #475569;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-secondary:hover {
            background: #e2e8f0;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid #e2e8f0;
            padding: 8px 15px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.3s;
        }

        .btn-outline:hover {
            border-color: #667eea;
            color: #667eea;
        }

        /* Two Column Grid for Forms */
        .two-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        /* Alert */
        .alert {
            padding: 12px 15px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }
            .sidebar {
                left: -260px;
            }
            .sidebar.active {
                left: 0;
            }
            .main-content {
                margin-left: 0;
                padding: 70px 20px 20px;
            }
            .sidebar-toggle.sidebar-open {
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.2s ease;
            }
        }

        .sidebar-toggle {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            width: 48px;
            height: 48px;
            border: none;
            border-radius: 14px;
            background: #667eea;
            color: #fff;
            font-size: 1.35rem;
            line-height: 1;
            z-index: 2000;
            cursor: pointer;
            align-items: center;
            justify-content: center;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.18);
        }

        .sidebar-toggle:hover {
            background: #4f63d7;
        }
    </style>
</head>
<body>
    <!-- Mobile sidebar toggle (visible via CSS on small screens) -->
    <button id="sidebarToggle" class="sidebar-toggle" type="button" aria-label="Toggle menu" onclick="toggleSidebar()">☰</button>

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
        <a href="budget.php" class="nav-link"><i class="fas fa-dollar-sign"></i> Budget</a>
        <a href="profile.php" class="nav-link active"><i class="fas fa-user"></i> Profile</a>
        <a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
</div>

<!-- Main Content -->
<div class="main-content">
    
    <div class="top-navbar">
        <h4 class="mb-0">Profile</h4>
        <div>
            <span>Welcome, <?php echo $_SESSION['user_name']; ?></span>
        </div>
    </div>

    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="profile-grid">
        <!-- Left Column - Profile Photo -->
        <div class="profile-card">
            <h4><i class="fas fa-user-circle"></i> Profile Photo</h4>
            <div class="profile-photo">
                <img src="<?php echo $profile_photo; ?>" alt="Profile Photo" id="profileImage">
                <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                <p><?php echo ucfirst($user['role']); ?></p>
            </div>
            
            <!-- Photo Upload Form -->
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Change Profile Photo</label>
                    <input type="file" name="profile_photo" class="form-control" accept="image/*" style="padding: 10px; border: 2px solid #e2e8f0; border-radius: 12px;" required>
                </div>
                <button type="submit" name="upload_photo" class="btn-primary" style="width: 100%;">
                    <i class="fas fa-upload"></i> Upload Photo
                </button>
            </form>
            
            <div class="photo-actions">
               <button class="btn-outline" onclick="removePhoto()"><i class="fas fa-trash"></i> Remove</button>
            </div>
        </div>

        <!-- Right Column - Profile Info & Password -->
        <div>
            <!-- Profile Information -->
            <div class="profile-card" style="margin-bottom: 25px;">
                <h4><i class="fas fa-info-circle"></i> Personal Information</h4>
                <form method="POST">
                    <div class="two-columns">
                        <div class="form-group">
                            <label>Full Name</label>
                            <div class="input-group">
                                <i class="fas fa-user"></i>
                                <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Title / Position</label>
                            <div class="input-group">
                                <i class="fas fa-briefcase"></i>
                                <input type="text" name="title" value="<?php echo htmlspecialchars($user['title'] ?? 'Administrator'); ?>" placeholder="Your Title">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <div class="input-group">
                                <i class="fas fa-envelope"></i>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <div class="input-group">
                                <i class="fas fa-phone"></i>
                                <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="+92 123 4567890">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <div class="input-group">
                            <i class="fas fa-shield-alt"></i>
                            <input type="text" value="<?php echo ucfirst($user['role']); ?>" disabled style="background: #f8fafc; color: #64748b;">
                        </div>
                    </div>
                    <button type="submit" name="update_profile" class="btn-primary">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </form>
            </div>

            <!-- Change Password -->
            <div class="profile-card">
                <h4><i class="fas fa-lock"></i> Change Password</h4>
                <form method="POST">
                    <div class="form-group">
                        <label>Current Password</label>
                        <div class="input-group">
                            <i class="fas fa-key"></i>
                            <input type="password" name="current_password" placeholder="Enter current password" required>
                        </div>
                    </div>
                    <div class="two-columns">
                        <div class="form-group">
                            <label>New Password</label>
                            <div class="input-group">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="new_password" placeholder="New password" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Confirm Password</label>
                            <div class="input-group">
                                <i class="fas fa-check-circle"></i>
                                <input type="password" name="confirm_password" placeholder="Confirm password" required>
                            </div>
                        </div>
                    </div>
                    <button type="submit" name="change_password" class="btn-secondary">
                        <i class="fas fa-exchange-alt"></i> Change Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/script.js"></script>
<script>
function removePhoto() {
    if(confirm('Are you sure you want to remove your profile photo?')) {
        window.location.href = 'profile.php?remove_photo=1';
    }
}

document.addEventListener('click', function(event) {
    var sidebar = document.querySelector('.sidebar');
    var toggleBtn = document.getElementById('sidebarToggle');
    if (!sidebar || !toggleBtn) return;

    if (window.innerWidth <= 992 && sidebar.classList.contains('active')) {
        if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
            sidebar.classList.remove('active');
            if (typeof updateSidebarToggleButton === 'function') {
                updateSidebarToggleButton();
            }
        }
    }
});
</script>
</body>
</html>