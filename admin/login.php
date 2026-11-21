<?php
session_start();
require '../includes/db.php';

// If already logged in, redirect
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Please fill in both fields.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $user['username'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | D'FACEEE</title>
    <link rel="icon" type="image/svg+xml" href="../assets/images/logo.svg">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    
    <style>
        /* ─────────── BASE & RESET ─────────── */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        :root {
            --bg-black: #111111;
            --panel-charcoal: #1c1c1c;
            --gold: #C5A059;
            --gold-hover: #b08d4a;
            --text-cream: #FDFBF7;
            --text-muted: #a3a3a3;
            --input-bg: #262626;
            --input-border: #3f3f3f;
        }

        body {
            font-family: 'Lato', sans-serif;
            background-color: var(--bg-black);
            background-image: radial-gradient(circle at 50% 50%, rgba(197, 160, 89, 0.08), transparent 50%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-cream);
            padding: 20px;
        }

        h1, h2, h3 { font-family: 'Cinzel', serif; }

        /* ─────────── SPLIT CONTAINER ─────────── */
        .login-container {
            display: flex;
            width: 100%;
            max-width: 850px; /* Reduced from 1000px */
            background: var(--panel-charcoal);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
            border: 1px solid rgba(197, 160, 89, 0.2);
            min-height: 520px; /* Reduced from 600px */
        }

        /* ─────────── LEFT PANEL (BRANDING) ─────────── */
        .brand-panel {
            flex: 1;
            background: linear-gradient(145deg, #1f1f1f, #151515);
            padding: 40px; /* Reduced from 60px 50px */
            display: flex;
            flex-direction: column;
            position: relative;
            border-right: 1px solid rgba(197, 160, 89, 0.1);
        }

        .brand-tag {
            display: inline-block;
            background: rgba(197, 160, 89, 0.1);
            border: 1px solid var(--gold);
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--gold);
            align-self: flex-start;
            margin-bottom: auto;
        }

        .brand-content {
            margin-top: 30px;
            margin-bottom: auto;
        }

        .brand-content h1 {
            font-size: 2.8rem; /* Reduced from 3.5rem */
            font-weight: 700;
            letter-spacing: 2px;
            margin-bottom: 5px;
            color: var(--gold);
            text-transform: uppercase;
        }

        .brand-content p {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 35px;
            letter-spacing: 1px;
        }

        .features-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 15px; /* Reduced gap */
        }

        .features-list li {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.85rem; /* Reduced font size */
            color: #d4d4d4;
        }

        .check-icon {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: var(--gold);
            color: #111;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: bold;
            flex-shrink: 0;
        }

        .brand-footer {
            font-size: 0.75rem;
            color: #666;
            margin-top: 30px;
            font-style: italic;
        }

        /* ─────────── RIGHT PANEL (FORM) ─────────── */
        .form-panel {
            flex: 1;
            padding: 40px; /* Reduced from 60px */
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: var(--panel-charcoal);
        }

        .form-header {
            margin-bottom: 30px; /* Reduced from 40px */
        }

        .form-header h2 {
            font-size: 1.8rem; /* Reduced from 2.2rem */
            font-weight: 700;
            color: var(--text-cream);
            margin-bottom: 8px;
        }

        .form-header p {
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .error-box {
            background: rgba(220, 38, 38, 0.1);
            border: 1px solid rgba(220, 38, 38, 0.3);
            color: #f87171;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.8rem;
            text-align: center;
        }

        .input-group {
            margin-bottom: 20px; /* Reduced from 25px */
        }

        .input-group label {
            display: block;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--gold);
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper input {
            width: 100%;
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            color: var(--text-cream);
            padding: 12px 15px; /* Reduced from 16px 20px */
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: 'Lato', sans-serif;
            transition: all 0.3s ease;
        }

        .input-wrapper input:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(197, 160, 89, 0.15);
        }

        .show-password {
            position: absolute;
            right: 15px;
            font-size: 0.75rem;
            color: var(--text-muted);
            cursor: pointer;
            font-weight: 700;
        }
        .show-password:hover { color: var(--gold); }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            font-size: 0.8rem;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
            cursor: pointer;
        }

        .checkbox-wrapper input {
            accent-color: var(--gold);
            width: 14px;
            height: 14px;
            cursor: pointer;
        }

        .forgot-link {
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 600;
        }
        .forgot-link:hover { color: var(--gold); }

        .btn-submit {
            width: 100%;
            background: var(--gold);
            color: #111;
            border: none;
            padding: 14px; /* Reduced from 16px */
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.2s ease;
            margin-bottom: 25px;
            font-family: 'Cinzel', serif;
            letter-spacing: 1px;
        }

        .btn-submit:hover {
            background: var(--gold-hover);
            transform: translateY(-2px);
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            color: #666;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 2px;
            margin-bottom: 25px;
            text-transform: uppercase;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .divider::before { margin-right: 15px; }
        .divider::after { margin-left: 15px; }

        .btn-secondary {
            width: 100%;
            background: transparent;
            color: var(--text-muted);
            border: 1px solid var(--input-border);
            padding: 14px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            display: block;
            transition: all 0.3s ease;
            margin-bottom: 30px;
        }
        .btn-secondary:hover { 
            background: rgba(197, 160, 89, 0.05); 
            border-color: var(--gold);
            color: var(--gold);
        }

        .form-footer {
            display: flex;
            justify-content: center;
            gap: 20px;
            font-size: 0.7rem;
            color: #666;
        }
        .form-footer a {
            color: #666;
            text-decoration: none;
        }
        .form-footer a:hover { color: var(--gold); }

        /* ─────────── RESPONSIVE ─────────── */
        @media (max-width: 768px) {
            .login-container { flex-direction: column; max-width: 400px; min-height: auto; }
            .brand-panel { padding: 30px; border-right: none; border-bottom: 1px solid rgba(197, 160, 89, 0.1); }
            .brand-content h1 { font-size: 2rem; }
            .brand-content { margin-top: 15px; margin-bottom: 15px; }
            .form-panel { padding: 30px; }
        }
    </style>
</head>
<body>

<div class="login-container">
    
    <!-- Left Side: Branding -->
    <div class="brand-panel">
        <span class="brand-tag">Admin Portal</span>
        
        <div class="brand-content">
            <h1>D'FACEEE</h1>
            <p>ShamySpice Collection Management</p>
            
            <ul class="features-list">
                <li><span class="check-icon">✓</span> Manage Appointments</li>
                <li><span class="check-icon">✓</span> Update Service Catalog</li>
                <li><span class="check-icon">✓</span> Track Revenue & Deposits</li>
            </ul>
        </div>

        <div class="brand-footer">
            "Where looks says it all"
        </div>
    </div>

    <!-- Right Side: Login Form -->
    <div class="form-panel">
        <div class="form-header">
            <h2>Welcome back</h2>
            <p>Sign in to continue to your dashboard.</p>
        </div>

        <?php if ($error): ?>
            <div class="error-box"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <label>Username</label>
                <div class="input-wrapper">
                    <input type="text" name="username" placeholder="admin" required autofocus>
                </div>
            </div>

            <div class="input-group">
                <label>Password</label>
                <div class="input-wrapper">
                    <input type="password" id="passwordInput" name="password" placeholder="Enter your password" required>
                    <span class="show-password" onclick="togglePassword()">Show</span>
                </div>
            </div>

            <div class="form-options">
                <label class="checkbox-wrapper">
                    <input type="checkbox" name="remember"> Remember me
                </label>
                <a href="#" class="forgot-link">Forgot password?</a>
            </div>

            <button type="submit" class="btn-submit">Sign In</button>

            <div class="divider">Return</div>

            <a href="../index.php" class="btn-secondary">Back to Main Website</a>
        </form>

        <div class="form-footer">
            <span>&copy; <?php echo date("Y"); ?> D'FACEEE</span>
            <a href="#">Support</a>
            <a href="#">Privacy</a>
        </div>
    </div>

</div>

<script>
    function togglePassword() {
        const input = document.getElementById('passwordInput');
        const btn = document.querySelector('.show-password');
        if (input.type === 'password') {
            input.type = 'text';
            btn.textContent = 'Hide';
        } else {
            input.type = 'password';
            btn.textContent = 'Show';
        }
    }
</script>

</body>
</html>