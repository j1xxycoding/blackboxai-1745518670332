<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect('dashboard.php');
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];

    // Verify credentials
    if ($username === 'lynce' && $password === 'casatex2025') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        redirect('dashboard.php');
    } else {
        $error = 'Invalid username or password';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - LYNCE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        body {
            background-color: var(--light-gray);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-logo img {
            max-width: 150px;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 0.75rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-logo">
            <img src="https://jaxxy.space/botop/Lynce_10%20cm%20w.png" alt="LYNCE Logo">
        </div>

        <?php if (isset($error)): ?>
            <div class="error-message">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="admin-form">
            <div class="form-row">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-input" required>
            </div>

            <div class="form-row">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-input" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
        </form>
    </div>
</body>
</html>
