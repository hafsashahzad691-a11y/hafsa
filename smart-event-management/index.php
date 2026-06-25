<?php
include 'includes/db.php';
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
include 'includes/header.php';
?>

<div class="container">
    <div class="row min-vh-100 align-items-center">
        <div class="col-md-6 mx-auto text-center">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-body p-5">
                    <h1 class="display-4 mb-3" style="background: linear-gradient(135deg, #667eea, #764ba2); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                        Smart Event Management System
                    </h1>
                    <p class="lead mb-4">Manage your events, vendors, tasks, and budgets all in one place</p>
                    <div class="d-grid gap-3 d-sm-flex justify-content-sm-center">
                        <a href="login.php" class="btn btn-primary btn-lg px-4">Login</a>
                        <a href="register.php" class="btn btn-outline-secondary btn-lg px-4">Register</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>