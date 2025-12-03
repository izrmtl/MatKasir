<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: index.php');
    exit;
}

 $error = '';
 $success = '';

// Check for logout success
if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $success = 'Anda telah berhasil logout dari sistem.';
}

// Process login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config.php';

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $database = new Database();
    $db = $database->getConnection();

    // Prepare statement to prevent SQL injection
    $query = "SELECT id_user, username, password, nama_lengkap, level FROM users WHERE username = :username";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    // Check if user exists
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Password is correct, start a new session
            session_regenerate_id(true); // Prevent session fixation
            
            $_SESSION['logged_in'] = true;
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['level'] = $user['level'];
            $_SESSION['login_time'] = time();
            $_SESSION['last_activity'] = time();

            // Redirect to dashboard
            header('Location: index.php');
            exit;
        } else {
            // Password is not correct
            $error = 'Username atau password salah!';
        }
    } else {
        // Username does not exist
        $error = 'Username atau password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Aplikasi Kasir UMKM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #7e22ce 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* Animated background shapes */
        .bg-shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            animation: float 25s infinite ease-in-out;
        }

        .bg-shape:nth-child(1) {
            width: 400px;
            height: 400px;
            top: -200px;
            right: -200px;
            animation-delay: 0s;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.15) 0%, transparent 70%);
        }

        .bg-shape:nth-child(2) {
            width: 300px;
            height: 300px;
            bottom: -150px;
            left: -150px;
            animation-delay: 7s;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.15) 0%, transparent 70%);
        }

        .bg-shape:nth-child(3) {
            width: 200px;
            height: 200px;
            top: 30%;
            left: -100px;
            animation-delay: 14s;
            background: radial-gradient(circle, rgba(236, 72, 153, 0.1) 0%, transparent 70%);
        }

        .bg-shape:nth-child(4) {
            width: 250px;
            height: 250px;
            bottom: 20%;
            right: -125px;
            animation-delay: 21s;
            background: radial-gradient(circle, rgba(34, 211, 238, 0.1) 0%, transparent 70%);
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg) scale(1); }
            25% { transform: translateY(-40px) rotate(90deg) scale(1.05); }
            50% { transform: translateY(20px) rotate(180deg) scale(0.95); }
            75% { transform: translateY(-20px) rotate(270deg) scale(1.02); }
        }

        .login-wrapper {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 28px;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.4), 
                        0 0 0 1px rgba(255, 255, 255, 0.1) inset;
            overflow: hidden;
            max-width: 480px;
            width: 100%;
            position: relative;
            z-index: 1;
            animation: slideUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(40px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            padding: 45px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 60%);
            animation: pulse 4s ease-in-out infinite;
        }

        .login-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #fbbf24, #f59e0b, #fbbf24);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.4; }
            50% { transform: scale(1.1); opacity: 0.2; }
        }

        .login-icon {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.25), rgba(255, 255, 255, 0.1));
            backdrop-filter: blur(10px);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 2.8em;
            color: white;
            position: relative;
            z-index: 1;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }

        .login-icon:hover {
            transform: scale(1.1) rotate(8deg);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
        }

        .login-header h1 {
            color: white;
            font-size: 2em;
            font-weight: 800;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .login-header p {
            color: rgba(255, 255, 255, 0.95);
            font-size: 1em;
            position: relative;
            z-index: 1;
            font-weight: 400;
        }

        .login-body {
            padding: 45px 35px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        .form-group {
            margin-bottom: 28px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 700;
            color: #374151;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            transition: all 0.3s;
            font-size: 1.1em;
        }

        .form-group input {
            width: 100%;
            padding: 16px 18px 16px 50px;
            border: 2px solid #e5e7eb;
            border-radius: 16px;
            font-size: 1em;
            transition: all 0.3s;
            background: #ffffff;
            color: #1f2937;
        }

        .form-group input::placeholder {
            color: #9ca3af;
        }

        .form-group input:focus {
            outline: none;
            border-color: #7e22ce;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(126, 34, 206, 0.1);
            transform: translateY(-2px);
        }

        .form-group input:focus + .input-icon {
            color: #7e22ce;
            transform: translateY(-50%) scale(1.1);
        }

        .btn-login {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #7e22ce 0%, #a855f7 50%, #ec4899 100%);
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 1.1em;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.4s;
            position: relative;
            overflow: hidden;
            margin-top: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 8px 24px rgba(126, 34, 206, 0.3);
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.6s;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 32px rgba(126, 34, 206, 0.4);
        }

        .btn-login:active {
            transform: translateY(-1px);
        }

        .message {
            padding: 16px 22px;
            border-radius: 16px;
            margin-bottom: 28px;
            text-align: center;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            animation: slideDown 0.5s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .error-message {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            box-shadow: 0 8px 24px rgba(239, 68, 68, 0.3);
        }

        .success-message {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.3);
        }

        .message i {
            font-size: 1.3em;
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 35px 0 25px;
            gap: 18px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 2px;
            background: linear-gradient(90deg, transparent, #e5e7eb, transparent);
        }

        .divider span {
            color: #6b7280;
            font-size: 0.85em;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            font-weight: 600;
        }

        .credentials-hint {
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
            border: 2px solid #0ea5e9;
            border-radius: 16px;
            padding: 20px;
            margin-top: 25px;
            position: relative;
            overflow: hidden;
        }

        .credentials-hint::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #0ea5e9, #06b6d4, #0ea5e9);
            animation: shimmer 3s infinite;
        }

        .credentials-hint-title {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #0369a1;
            font-weight: 700;
            margin-bottom: 12px;
            font-size: 0.95em;
        }

        .credentials-hint-content {
            color: #475569;
            font-size: 0.9em;
            line-height: 1.7;
        }

        .credentials-hint-content strong {
            color: #1e293b;
            font-weight: 700;
        }

        .login-footer {
            text-align: center;
            margin-top: 35px;
            color: #64748b;
            font-size: 0.85em;
            font-weight: 500;
        }

        .login-footer i {
            color: #ef4444;
            animation: heartbeat 1.5s ease-in-out infinite;
        }

        @keyframes heartbeat {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }

        /* Responsive Design */
        @media (max-width: 480px) {
            .login-wrapper {
                border-radius: 20px;
            }
            
            .login-header {
                padding: 35px 25px;
            }
            
            .login-body {
                padding: 35px 25px;
            }

            .login-header h1 {
                font-size: 1.7em;
            }
        }
    </style>
</head>
<body>
    <!-- Animated background shapes -->
    <div class="bg-shape"></div>
    <div class="bg-shape"></div>
    <div class="bg-shape"></div>
    <div class="bg-shape"></div>

    <div class="login-wrapper">
        <div class="login-header">
            <div class="login-icon">
                <i class="fas fa-cash-register"></i>
            </div>
            <h1>Aplikasi Kasir UMKM</h1>
            <p>Silakan login untuk melanjutkan</p>
        </div>
        
        <div class="login-body">
            <?php if ($error): ?>
                <div class="message error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="message success-message">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <input type="text" id="username" name="username" required autocomplete="username" placeholder="Masukkan username">
                        <i class="fas fa-user input-icon"></i>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="Masukkan password">
                        <i class="fas fa-lock input-icon"></i>
                    </div>
                </div>
                
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>


            <div class="login-footer">
                &copy; 2025 Aplikasi Kasir UMKM | Dibuat dengan untuk UMKM Indonesia
            </div>
        </div>
    </div>
</body>
</html>