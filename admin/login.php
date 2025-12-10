<?php
session_start();
if (isset($_SESSION['admin_logged_in'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Simple authentication
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Incorrect username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Heart of D' Ocean</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1e3c72;
            --secondary: #2a5298;
            --accent: #00d2ff;
            --text: #333;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        
        body { 
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            padding: 20px; 
            position: relative;
            overflow: hidden;
        }

        body::before, body::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            z-index: 0;
        }
        body::before { width: 300px; height: 300px; top: -50px; left: -50px; }
        body::after { width: 400px; height: 400px; bottom: -100px; right: -100px; }

        .login-container { 
            background: rgba(255, 255, 255, 0.95); 
            padding: 3.5rem 3rem; 
            border-radius: 20px; 
            box-shadow: 0 20px 50px rgba(0,0,0,0.25); 
            width: 100%; 
            max-width: 450px; 
            position: relative;
            z-index: 1;
            text-align: center;
            backdrop-filter: blur(10px); 
        }

        .logo-area {
            margin-bottom: 2rem;
        }

        .logo-area img {
            width: 350px;  
            height: auto;
            margin-bottom: 1.5rem;
            filter: drop-shadow(0 5px 15px rgba(0,0,0,0.2)); 
            transition: transform 0.3s;
}
        .login-container h2 { 
            color: var(--primary); 
            font-weight: 700; 
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .subtitle {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 2.5rem;
        }

        .form-group { 
            margin-bottom: 1.5rem; 
            position: relative;
            text-align: left;
        }

        .form-group label { 
            display: block; 
            margin-bottom: 0.5rem; 
            color: #444; 
            font-weight: 600; 
            font-size: 0.9rem; 
            margin-left: 5px;
        }

        /* Input with Icon Styling */
        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            transition: color 0.3s;
        }

        .form-group input { 
            width: 100%; 
            padding: 14px 15px 14px 45px; 
            border: 2px solid #e1e5e9; 
            border-radius: 50px; 
            font-size: 15px; 
            transition: all 0.3s; 
            background: #f8f9fa;
        }

        .form-group input:focus { 
            outline: none; 
            border-color: var(--accent); 
            background: #fff;
            box-shadow: 0 0 0 4px rgba(0, 210, 255, 0.1);
        }

        .form-group input:focus + i {
            color: var(--accent);
        }

        .btn-login { 
            width: 100%; 
            background: linear-gradient(to right, var(--secondary), var(--primary)); 
            color: white; 
            border: none; 
            padding: 15px; 
            border-radius: 50px; 
            font-size: 16px; 
            font-weight: 600; 
            cursor: pointer; 
            transition: transform 0.2s, box-shadow 0.2s; 
            margin-top: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-login:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 10px 20px rgba(30, 60, 114, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .error { 
            color: #e74c3c; 
            background: #fdf0f0; 
            padding: 12px; 
            border-radius: 8px; 
            font-size: 0.9rem; 
            margin-bottom: 1.5rem; 
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border-left: 4px solid #e74c3c;
        }

        .demo-credentials { 
            margin-top: 2rem; 
            padding-top: 1.5rem; 
            border-top: 1px solid #eee; 
            font-size: 0.85rem; 
            color: #888; 
        }
        
        .footer-link {
            display: block;
            margin-top: 1.5rem;
            color: var(--secondary);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .footer-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-area">
            <img src="../images&vids/logo1.png" alt="Resort Logo">
        </div>

        <h2>Welcome Back</h2>
        <p class="subtitle">Please login to your admin account</p>
        
        <?php if ($error): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-wrapper">
                    <input type="text" id="username" name="username" placeholder="Enter your username" required autocomplete="username">
                    <i class="fas fa-user"></i>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
                    <i class="fas fa-lock"></i>
                </div>
            </div>
            
            <button type="submit" class="btn-login">
                Sign In <i class="fas fa-arrow-right" style="margin-left: 8px;"></i>
            </button>
        </form>
        
        <a href="../index.php" class="footer-link">← Back to Website</a>

        <div class="demo-credentials">
            Admin Access<br>
            User: <strong>admin</strong> | Pass: <strong>admin123</strong>
        </div>
    </div>
</body>
</html>