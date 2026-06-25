<?php
include 'includes/db.php';

$error = '';
$success = '';

if(isset($_POST['reset'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
    
    if(mysqli_num_rows($check) == 1) {
        // Generate random password
        $new_password = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz1234567890"), 0, 8);
        $hashed_password = md5($new_password);
        
        mysqli_query($conn, "UPDATE users SET password='$hashed_password' WHERE email='$email'");
        $success = "Your new password is: <strong>$new_password</strong><br>Please login and change it.";
    } else {
        $error = "Email not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background: linear-gradient(135deg, #667eea, #764ba2); min-height: 100vh;">
    <div class="container d-flex align-items-center justify-content-center min-vh-100">
        <div class="card p-4" style="width: 400px; border-radius: 20px;">
            <h3 class="text-center mb-3">Reset Password</h3>
            
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <a href="login.php" class="btn btn-primary w-100">Go to Login</a>
            <?php else: ?>
                <form method="POST">
                    <input type="email" name="email" class="form-control mb-3" placeholder="Enter your email" required>
                    <button type="submit" name="reset" class="btn btn-primary w-100">Reset Password</button>
                    <a href="login.php" class="btn btn-link w-100 mt-2">Back to Login</a>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>