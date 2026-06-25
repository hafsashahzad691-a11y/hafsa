<?php
include 'includes/db.php';

if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = md5($_POST['password']);
    $confirm_password = md5($_POST['confirm_password']);
    
    if(empty($name) || empty($email) || empty($_POST['password'])) {
        $error = "All fields are required!";
    } elseif($_POST['password'] !== $_POST['confirm_password']) {
        $error = "Passwords do not match!";
    } else {
        $check_query = "SELECT id FROM users WHERE email = '$email'";
        $check_result = mysqli_query($conn, $check_query);
        
        if(mysqli_num_rows($check_result) > 0) {
            $error = "Email already registered!";
        } else {
            $query = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$password', 'user')";
            if(mysqli_query($conn, $query)) {
                $success = "Registration successful! You can now login.";
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Smart Event Management System</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Register Container */
        .register-container {
            width: 100%;
            max-width: 1000px;
            padding: 20px;
        }

        /* Register Card with Image */
        .register-card {
            background: white;
            border-radius: 30px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            display: flex;
            flex-wrap: wrap;
        }

        /* Left Side - Image */
        .register-image {
            flex: 1;
            background: linear-gradient(135deg, #667eea, #764ba2);
            padding: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }

        .register-image img {
            width: 80%;
            max-width: 250px;
            margin-bottom: 30px;
        }

        .register-image h3 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .register-image p {
            font-size: 0.9rem;
            opacity: 0.9;
            line-height: 1.6;
        }

        .register-image .features {
            margin-top: 30px;
            text-align: left;
        }

        .register-image .features li {
            margin-bottom: 10px;
            list-style: none;
        }

        .register-image .features i {
            margin-right: 10px;
        }

        /* Right Side - Form */
        .register-form {
            flex: 1;
            padding: 40px;
        }

        /* Logo */
        .logo {
            text-align: center;
            margin-bottom: 25px;
        }

        .logo i {
            font-size: 2.5rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .logo h2 {
            font-size: 1.2rem;
            font-weight: 700;
            margin-top: 8px;
            color: #1e293b;
        }

        .logo p {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 3px;
        }

        /* Form Groups */
        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #475569;
            font-size: 0.8rem;
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
            padding: 0 12px;
            color: #94a3b8;
            font-size: 0.9rem;
        }

        .input-group input {
            flex: 1;
            padding: 12px 12px 12px 0;
            border: none;
            outline: none;
            font-size: 0.9rem;
            background: transparent;
        }

        /* Register Button */
        .btn-register {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
            margin-bottom: 20px;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(102, 126, 234, 0.4);
        }

        /* Login Link */
        .login-link {
            text-align: center;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
        }

        .login-link p {
            color: #64748b;
            font-size: 0.85rem;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 700;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        /* Alert */
        .alert {
            padding: 10px 12px;
            border-radius: 10px;
            margin-bottom: 15px;
            font-size: 0.8rem;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        /* Divider */
        .divider {
            text-align: center;
            margin: 15px 0;
            position: relative;
        }

        .divider::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e2e8f0;
        }

        .divider span {
            background: white;
            padding: 0 12px;
            position: relative;
            color: #94a3b8;
            font-size: 0.75rem;
        }

        /* Password Hint */
        .password-hint {
            font-size: 0.65rem;
            color: #94a3b8;
            margin-top: 5px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .register-image {
                display: none;
            }
            .register-form {
                flex: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <!-- Left Side - Image -->
            <div class="register-image">
                <img src="https://img.icons8.com/fluency/200/add-user-male.png" alt="Register">
                <h3>Join Us!</h3>
                <p>Create your account and start managing your events professionally.</p>
                <ul class="features">
                    <li><i class="fas fa-check-circle"></i> Easy Registration</li>
                    <li><i class="fas fa-check-circle"></i> Manage Events</li>
                    <li><i class="fas fa-check-circle"></i> Track Tasks</li>
                    <li><i class="fas fa-check-circle"></i> Control Budget</li>
                </ul>
            </div>

            <!-- Right Side - Form -->
            <div class="register-form">
                <div class="logo">
                    <i class="fas fa-user-plus"></i>
                    <h2>Create Account</h2>
                    <p>Register to get started</p>
                </div>

                <?php if($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                        <a href="login.php" style="color: #065f46; font-weight: 600; display: block; margin-top: 5px;">Click here to Login</a>
                    </div>
                <?php endif; ?>

                <form method="POST" onsubmit="return validateForm('registerForm')" id="registerForm">
                    <div class="form-group">
                        <label>Full Name</label>
                        <div class="input-group">
                            <i class="fas fa-user"></i>
                            <input type="text" name="name" placeholder="Enter your full name" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <div class="input-group">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" placeholder="Enter your email" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" id="password" placeholder="Create a password" required>
                        </div>
                        <div class="password-hint">
                            <i class="fas fa-info-circle"></i> Password must be at least 4 characters
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Confirm Password</label>
                        <div class="input-group">
                            <i class="fas fa-check-circle"></i>
                            <input type="password" name="confirm_password" placeholder="Confirm your password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn-register">
                        <i class="fas fa-user-plus"></i> Register Now
                    </button>
                </form>

                <div class="divider">
                    <span>ALREADY HAVE AN ACCOUNT?</span>
                </div>

                <div class="login-link">
                    <p><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login to your account</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>