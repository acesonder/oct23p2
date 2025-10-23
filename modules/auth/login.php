<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: /modules/dashboard/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        $conn = getConnection();
        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->bind_param("ss", $username, $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                if ($user['status'] === 'inactive') {
                    $error = 'Your account has been deactivated. Please contact administrator.';
                } elseif (password_verify($password, $user['password'])) {
                    // Update last login
                    $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                    $updateStmt->bind_param("i", $user['id']);
                    $updateStmt->execute();
                    
                    // Set session
                    setUserSession($user);
                    
                    // Redirect based on role
                    if ($user['role'] === 'admin') {
                        header('Location: /admin/index.php');
                    } else {
                        header('Location: /modules/dashboard/index.php');
                    }
                    exit;
                } else {
                    $error = 'Invalid username or password';
                }
            } else {
                $error = 'Invalid username or password';
            }
            
            $stmt->close();
            closeConnection($conn);
        } else {
            $error = 'Database connection error. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - OUTSINC</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <h1>OUTSINC</h1>
                <p>Outreach Someone In Need Of Change</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="username">Username or Email</label>
                    <input type="text" class="form-control" id="username" name="username" 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required autofocus>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
            
            <div class="auth-footer">
                <p>Default credentials: <strong>admin / admin123</strong></p>
                <a href="/index.html">← Back to Home</a>
            </div>
        </div>
    </div>
</body>
</html>
