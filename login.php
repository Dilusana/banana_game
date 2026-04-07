<?php 
    session_start(); 
    ob_start(); 

    $error_msg = "";

    // 1. Login Logic
    if ($_SERVER['REQUEST_METHOD'] == 'POST') { 
        $username = isset($_POST['username']) ? $_POST['username'] : ""; 
        $password = isset($_POST['password']) ? $_POST['password'] : ""; 

        // 2. Database Connection
        $conn = new mysqli("localhost", "root", "", "banana_game");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // 3. Query the 'users' table for the matching username and password
        $stmt = $conn->prepare("SELECT username FROM users WHERE username = ? AND password = ?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) { 
            // Successful Login
            $_SESSION['username'] = $username; 
            
            header("Location: gamemap.php"); 
            exit(); 
        } else { 
            // Failed Login
            $error_msg = "Invalid username or password. Please try again."; 
        }

        $stmt->close();
        $conn->close();
    } 
    ob_end_flush(); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Banana Game - Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', 'Segoe UI', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #1a2a1f 0%, #0e1a0c 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
        }

        /* Animated banana background pattern */
        body::before {
            content: "🍌";
            font-size: 180px;
            position: fixed;
            bottom: -30px;
            left: -50px;
            opacity: 0.08;
            pointer-events: none;
            animation: floatBanana 20s infinite ease-in-out;
            z-index: 0;
        }

        body::after {
            content: "🍌";
            font-size: 140px;
            position: fixed;
            top: 10%;
            right: -40px;
            opacity: 0.08;
            pointer-events: none;
            animation: floatBanana 25s infinite reverse ease-in-out;
            z-index: 0;
        }

        @keyframes floatBanana {
            0% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(30px, -20px) rotate(10deg); }
            100% { transform: translate(0, 0) rotate(0deg); }
        }

        /* Main card container */
        .login-card {
            background: rgba(30, 35, 25, 0.85);
            backdrop-filter: blur(12px);
            border-radius: 48px;
            padding: 40px 35px 45px;
            width: 100%;
            max-width: 520px;
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(241, 196, 15, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            z-index: 2;
            border: 1px solid rgba(241, 196, 15, 0.4);
        }

        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 30px 55px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(241, 196, 15, 0.5);
        }

        /* Header with banana emoji */
        .banana-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 15px;
        }

        .banana-header h2 {
            font-size: 2.4rem;
            font-weight: 700;
            background: linear-gradient(135deg, #FFE55C 0%, #FFC107 100%);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
            letter-spacing: -0.5px;
        }

        .banana-icon {
            font-size: 2.8rem;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.3));
            animation: wiggle 2.5s infinite ease;
        }

        @keyframes wiggle {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(12deg); }
            75% { transform: rotate(-8deg); }
        }

        .subtitle {
            text-align: center;
            color: #d4d9bf;
            margin-bottom: 32px;
            font-size: 0.95rem;
            border-bottom: 1px dashed rgba(241,196,15,0.4);
            display: inline-block;
            width: auto;
            padding-bottom: 6px;
        }

        /* Form styling */
        .input-group {
            margin-bottom: 20px;
            position: relative;
        }

        .input-group label {
            display: block;
            color: #e9f5db;
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }

        .input-group input {
            width: 100%;
            padding: 16px 20px 16px 55px;
            background: #2a2f24;
            border: 2px solid #3e4236;
            border-radius: 60px;
            font-size: 1rem;
            color: #f5f5e9;
            transition: all 0.25s ease;
            font-family: inherit;
            outline: none;
        }

        .input-group input:focus {
            border-color: #f1c40f;
            box-shadow: 0 0 0 3px rgba(241, 196, 15, 0.3);
            background: #1f241c;
        }

        .input-group .input-icon {
            position: absolute;
            left: 22px;
            top: 70%;
            transform: translateY(-50%);
            font-size: 1.3rem;
            pointer-events: none;
        }

        /* Button styles */
        .btn-play {
            width: 100%;
            background: linear-gradient(95deg, #f1c40f, #e67e22);
            border: none;
            padding: 15px 20px;
            border-radius: 60px;
            font-weight: 800;
            font-size: 1.2rem;
            color: #1e2a1a;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 12px;
            margin-bottom: 20px;
            font-family: inherit;
            box-shadow: 0 6px 0 #b45f1b;
            letter-spacing: 1px;
        }

        .btn-play:active {
            transform: translateY(3px);
            box-shadow: 0 2px 0 #b45f1b;
        }

        .btn-create {
            width: 100%;
            background: transparent;
            border: 2px solid #f1c40f;
            padding: 12px 20px;
            border-radius: 60px;
            font-weight: 600;
            font-size: 1rem;
            color: #ffea9e;
            cursor: pointer;
            transition: all 0.25s ease;
            font-family: inherit;
            margin-top: 5px;
        }

        .btn-create:hover {
            background: rgba(241, 196, 15, 0.15);
            transform: scale(1.02);
            border-color: #ffda44;
            color: #ffffff;
        }

 

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(4px);
            padding: 10px 18px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: 0.25s;
            border: 1px solid rgba(241,196,15,0.5);
            color: #ffea9e;
        }

        .action-btn:hover {
            background: #f1c40f;
            color: #1f2a0e;
            border-color: #f1c40f;
            transform: scale(1.02);
        }

        /* Error message styling */
        .error-message {
            text-align: center;
            margin: 12px 0 5px;
            font-weight: 500;
            background: rgba(0,0,0,0.6);
            padding: 10px 15px;
            border-radius: 60px;
            font-size: 0.9rem;
            color: #ffaeae;
            border-left: 3px solid #ff6b6b;
        }

        .footer-note {
            font-size: 0.7rem;
            text-align: center;
            margin-top: 25px;
            color: #9eaa8b;
        }

        .help-text {
            display: block;
            text-align: center;
            color: #b8c4a0;
            font-size: 0.85rem;
            margin: 15px 0 8px;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="banana-header">
        <span class="banana-icon">🍌</span>
        <h2>Jungle Vault</h2>
        <span class="banana-icon">🍌</span>
    </div>
    <div style="text-align: center;">
        <div class="subtitle">✨ Enter the banana paradise ✨</div>
    </div>

    <?php if($error_msg): ?>
        <div class="error-message">
            ⚠️ <?php echo $error_msg; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="input-group">
            <label>🐒 Username</label>
            <span class="input-icon">🐵</span>
            <input type="text" name="username" required placeholder="Enter your jungle name">
        </div>

        <div class="input-group">
            <label>🔐 Password</label>
            <span class="input-icon">🔑</span>
            <input type="password" name="password" required placeholder="Enter secret code">
        </div>

        <button type="submit" class="btn-play">🍌 Play Now! 🍌</button>
        
        <span class="help-text">🌴 New to the jungle? 🌴</span>
        <button type="button" class="btn-create" onclick="window.location.href='register.php'">✨ Create Account ✨</button>
        <button type="button" class="btn-create" onclick="window.location.href='index.php'">Menu</button>

    </form>


    <div class="footer-note">
        🍌 Collect bananas, climb the leaderboard! 🍌
    </div>
</div>

</body>
</html>