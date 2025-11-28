<?php
session_start();
require_once 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit;
}

$login_error = '';
if (isset($_POST['login'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username=?");
    $stmt->execute([$_POST['username']]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['user'] = $user['username'];
        header("Location: dashboard.php");
        exit;
    } else {
        $login_error = '❌ Username cyangwa password siyo!';
    }
}
?>

<!DOCTYPE html>
<html lang="rw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GS Nyagisozi</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{
            font-family:'Inter',sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            overflow:hidden;
            position:relative;
        }
        body::before{
            content:'';
            position:absolute;
            top:0;left:0;right:0;bottom:0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><radialGradient id="a" cx="50%" cy="50%"><stop offset="0%" stop-color="%23ffffff15"/><stop offset="100%" stop-color="%23ffffff00"/></radialGradient></defs>ircle cx="300" cy="200" r="150" fill="url(%23a)"><animate attributeName="r" values="150;200;150" dur="8s" repeatCount="indefinite"/><animate attributeName="cx" values="300;400;300" dur="8s" repeatCount="indefinite"/><animateTransform attributeName="transform" type="rotate" values="0 300 200;360 300 200" dur="20s" repeatCount="indefinite"/></circle>ircle cx="700" cy="600" r="120" fill="url(%23a)"><animate attributeName="r" values="120;160;120" dur="10s" repeatCount="indefinite"/><animate attributeName="cx" values="700;800;700" dur="10s" repeatCount="indefinite"/><animateTransform attributeName="transform" type="rotate" values="0 700 600;360 700 600" dur="25s" repeatCount="indefinite"/></circle></svg>');
            animation: float 20s ease-in-out infinite;
        }
        @keyframes float{0%,100%{transform:translateY(0px) rotate(0deg);}50%{transform:translateY(-20px) rotate(180deg);}}
        
        .login-container{
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(25px);
            padding: 60px 50px;
            border-radius: 30px;
            box-shadow: 0 35px 80px rgba(0,0,0,0.25);
            width: 100%;
            max-width: 450px;
            text-align: center;
            position: relative;
            animation: slideUp 0.8s ease-out;
            border: 1px solid rgba(255,255,255,0.3);
        }
        @keyframes slideUp{from{opacity:0;transform:translateY(50px);}to{opacity:1;transform:translateY(0);}}
        
        .school-logo{
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #2c7be5, #1a5ddb);
            border-radius: 25px;
            margin: 0 auto 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
            color: white;
            box-shadow: 0 20px 50px rgba(44,123,229,0.4);
            animation: pulse 2s infinite;
        }
        @keyframes pulse{0%,100%{transform:scale(1);}50%{transform:scale(1.05);}}
        
        h2{
            font-size: 32px;
            font-weight: 900;
            background: linear-gradient(135deg, #1e293b, #334155);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }
        .subtitle{
            color: #64748b;
            font-weight: 500;
            margin-bottom: 40px;
            font-size: 16px;
        }
        
        .input-group{
            position: relative;
            margin-bottom: 25px;
        }
        .input-icon{
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 18px;
            z-index: 2;
        }
        input{
            width: 100%;
            padding: 20px 20px 20px 60px;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            font-size: 16px;
            font-weight: 500;
            background: #fff;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        input:focus{
            outline: none;
            border-color: #2c7be5;
            box-shadow: 0 10px 30px rgba(44,123,229,0.2);
            transform: translateY(-2px);
        }
        input::placeholder{color:#94a3b8;}
        
        .login-btn{
            width: 100%;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            padding: 20px;
            border-radius: 16px;
            font-size: 18px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 15px 35px rgba(16,185,129,0.4);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .login-btn:hover{
            transform: translateY(-3px);
            box-shadow: 0 20px 45px rgba(16,185,129,0.5);
        }
        .login-btn:active{transform:translateY(-1px);}
        
        .error-msg{
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #dc2626;
            padding: 18px;
            border-radius: 16px;
            margin-bottom: 25px;
            border-left: 6px solid #ef4444;
            font-weight: 600;
            animation: shake 0.5s ease-in-out;
            box-shadow: 0 5px 15px rgba(239,68,68,0.2);
        }
        @keyframes shake{0%,100%{transform:translateX(0);}25%{transform:translateX(-5px);}75%{transform:translateX(5px);}}
        
        .footer-text{
            margin-top: 30px;
            color: #64748b;
            font-size: 14px;
        }
        .demo-link{
            color: #2c7be5;
            text-decoration: none;
            font-weight: 600;
        }
        .demo-link:hover{text-decoration:underline;}
        
        @media (max-width: 480px){
            .login-container{padding:40px 30px;}
            h2{font-size:26px;}
            .school-logo{width:100px;height:100px;font-size:40px;}
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- School Logo -->
        <div class="school-logo">
            <i class="fas fa-graduation-cap"></i>
        </div>
        
        <!-- Title -->
        <h2>GS Nyagisozi</h2>
        <p class="subtitle">Ikigo cy'Abanyeshuri & Abakozi</p>
        
        <!-- Error Message -->
        <?php if($login_error): ?>
        <div class="error-msg"><?= $login_error ?></div>
        <?php endif; ?>
        
        <!-- Login Form -->
        <form method="POST">
            <div class="input-group">
                <i class="fas fa-user input-icon"></i>
                <input type="text" name="username" placeholder="👤 Username" required autocomplete="username">
            </div>
            
            <div class="input-group">
                <i class="fas fa-lock input-icon"></i>
                <input type="password" name="password" placeholder="🔒 Password" required autocomplete="current-password">
            </div>
            
            <button type="submit" name="login" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> Kwinjira
            </button>
        </form>
        
        <!-- Footer -->
        <div class="footer-text">
            Demo: <a href="#" class="demo-link" onclick="demoLogin()">admin / 123456</a>
        </div>
    </div>

    <script>
        // Demo login function
        function demoLogin() {
            document.querySelector('input[name="username"]').value = 'admin';
            document.querySelector('input[name="password"]').value = '123456';
        }
        
        // Auto-focus first input
        document.querySelector('input[name="username"]').focus();
        
        // Enter key submits form
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.querySelector('form').submit();
            }
        });
    </script>
</body>
</html>
