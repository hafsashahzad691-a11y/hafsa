<?php
include 'includes/db.php';

if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = md5($_POST['password']);
    
    $query = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
    $result = mysqli_query($conn, $query);
    
    if(mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid email or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Smart Event Management System</title>
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

        /* Login Container */
        .login-container {
            width: 100%;
            max-width: 1000px;
            padding: 20px;
        }

        /* Login Card with Image */
        .login-card {
            background: white;
            border-radius: 30px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            display: flex;
            flex-wrap: wrap;
        }

        /* Left Side - Image */
        .login-image {
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

        .login-image img {
            width: 80%;
            max-width: 250px;
            margin-bottom: 30px;
        }

        .login-image h3 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .login-image p {
            font-size: 0.9rem;
            opacity: 0.9;
            line-height: 1.6;
        }

        .login-image .features {
            margin-top: 30px;
            text-align: left;
        }

        .login-image .features li {
            margin-bottom: 10px;
            list-style: none;
        }

        .login-image .features i {
            margin-right: 10px;
        }

        /* Right Side - Form */
        .login-form {
            flex: 1;
            padding: 40px;
        }

        /* Logo */
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo i {
            font-size: 3rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .logo h2 {
            font-size: 1.3rem;
            font-weight: 700;
            margin-top: 10px;
            color: #1e293b;
        }

        .logo p {
            font-size: 0.8rem;
            color: #94a3b8;
            margin-top: 5px;
        }

        /* Form Groups */
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
            border-radius: 15px;
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
            font-size: 1rem;
        }

        .input-group input {
            flex: 1;
            padding: 14px 15px 14px 0;
            border: none;
            outline: none;
            font-size: 0.95rem;
            background: transparent;
        }

        /* Forgot Password */
        .forgot-link {
            text-align: right;
            margin-bottom: 25px;
        }

        .forgot-link a {
            color: #667eea;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .forgot-link a:hover {
            text-decoration: underline;
        }

        /* Login Button */
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 15px;
            color: white;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 25px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(102, 126, 234, 0.4);
        }

        /* Register Link */
        .register-link {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }

        .register-link p {
            color: #64748b;
            font-size: 0.9rem;
        }

        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 700;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        /* Alert */
        .alert {
            padding: 12px 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 0.85rem;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        /* Divider */
        .divider {
            text-align: center;
            margin: 20px 0;
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
            padding: 0 15px;
            position: relative;
            color: #94a3b8;
            font-size: 0.8rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .login-image {
                display: none;
            }
            .login-form {
                flex: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Left Side - Image -->
            <div class="login-image">
                <img src="https://img.icons8.com/fluency/200/calendar.png" alt="Calendar">
                <h3>Welcome Back!</h3>
                <p>Manage your events, vendors, tasks and budgets all in one place.</p>
                <ul class="features">
                    <li><i class="fas fa-check-circle"></i> Event Management</li>
                    <li><i class="fas fa-check-circle"></i> Vendor Management</li>
                    <li><i class="fas fa-check-circle"></i> Task Tracking</li>
                    <li><i class="fas fa-check-circle"></i> Budget Planning</li>
                </ul>
            </div>

            <!-- Right Side - Form -->
            <div class="login-form">
                <div class="logo">
                    <i class="fas fa-calendar-alt"></i>
                    <h2>Smart Event<br>Management System</h2>
                    <p>Login to your account</p>
                </div>

                <?php if($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" id="loginForm">
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
                            <input type="password" name="password" placeholder="Enter your password" required>
                        </div>
                    </div>

                  <div class="forgot-link">
    <a href="forgot_password.php">Forgot Password?</a>
</div>

                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i> Log In
                    </button>
                </form>

                <div class="divider">
                    <span>OR</span>
                </div>

                <div class="register-link">
                    <p>Don't have an account? <a href="register.php">Register now</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>