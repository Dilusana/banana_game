<?php
session_start();

// Initialize variables
$popup_trigger = false;
$inline_error = "";
$preserve_username = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = trim($_POST['username']);
    $pass = $_POST['password'];
    $preserve_username = htmlspecialchars($user);

    // Basic validation
    if (empty($user) || empty($pass)) {
        $inline_error = "<span>⚠️</span> Username & password required!";
    } else {
        $conn = new mysqli("localhost", "root", "", "banana_game");

        if ($conn->connect_error) {
            $inline_error = "Database connection failed.";
        } else {
            // Check if username exists
            $checkUser = $conn->prepare("SELECT username FROM users WHERE username = ?");
            $checkUser->bind_param("s", $user);
            $checkUser->execute();
            $result = $checkUser->get_result();

            if ($result->num_rows > 0) {
                $inline_error = "🍌 Username already taken! Try another one.";
            } else {
                // Start transaction for users + leaderboard
                $conn->begin_transaction();
                try {
                    $stmt1 = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                    $stmt1->bind_param("ss", $user, $pass);
                    $stmt1->execute();

                    $stmt2 = $conn->prepare("INSERT INTO leaderboard (username, bananas) VALUES (?, 0)");
                    $stmt2->bind_param("s", $user);
                    $stmt2->execute();

                    $conn->commit();
                    // SUCCESS! Trigger popup flag
                    $popup_trigger = true;
                } catch (Exception $e) {
                    $conn->rollback();
                    $inline_error = "System error: account could not be created.";
                }
            }
            $conn->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Create Account - Banana Jungle</title>
    
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
        .register-card {
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

        .register-card:hover {
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
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.3rem;
            pointer-events: none;
        }

        /* Button */
        .create-btn {
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

        .create-btn:active {
            transform: translateY(3px);
            box-shadow: 0 2px 0 #b45f1b;
        }

        /* Action links container */
        .action-links {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-top: 15px;
            flex-wrap: wrap;
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

        .menu-icon-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        /* MODAL / POPUP STYLES - Modern, not closing immediately */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(8px);
            z-index: 1000;
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0s linear 0.3s;
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
            transition: opacity 0.3s ease, visibility 0s linear 0s;
        }

        .success-popup {
            background: linear-gradient(145deg, #2b3524, #1c2618);
            max-width: 400px;
            width: 90%;
            border-radius: 48px;
            text-align: center;
            padding: 30px 25px 35px;
            box-shadow: 0 25px 45px rgba(0,0,0,0.5), 0 0 0 2px #f1c40f80;
            transform: scale(0.8);
            transition: transform 0.3s cubic-bezier(0.34, 1.2, 0.64, 1);
            border: 1px solid rgba(241,196,15,0.7);
        }

        .modal-overlay.active .success-popup {
            transform: scale(1);
        }

        .popup-banana {
            font-size: 5rem;
            animation: bouncePop 0.5s ease-out;
            margin-bottom: 8px;
        }

        @keyframes bouncePop {
            0% { transform: scale(0.3); opacity: 0; }
            70% { transform: scale(1.1); }
            100% { transform: scale(1); opacity: 1; }
        }

        .popup-title {
            font-size: 1.9rem;
            font-weight: 800;
            background: linear-gradient(135deg, #FFE074, #ffb347);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
            margin: 10px 0 8px;
        }

        .popup-message {
            color: #e9f5db;
            font-size: 1.1rem;
            margin: 15px 0 10px;
            line-height: 1.4;
        }

        .popup-login-btn {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: #f1c40f;
            padding: 12px 28px;
            border-radius: 60px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.2rem;
            margin-top: 20px;
            color: #1e2a1a;
            transition: 0.2s;
            font-family: inherit;
        }

        .popup-login-btn:hover {
            background: #ffda44;
            transform: scale(1.02);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        .close-popup {
            background: transparent;
            border: 1px solid #f1c40f;
            color: #f1c40f;
            padding: 8px 20px;
            border-radius: 40px;
            margin-top: 18px;
            cursor: pointer;
            font-size: 0.85rem;
            transition: 0.2s;
            font-weight: 500;
        }

        .close-popup:hover {
            background: rgba(241, 196, 15, 0.2);
            transform: scale(1.02);
        }

        /* Alert message styling */
        .inline-message {
            text-align: center;
            margin: 12px 0 5px;
            font-weight: 500;
            background: rgba(0,0,0,0.6);
            padding: 10px 15px;
            border-radius: 60px;
            font-size: 0.9rem;
            color: #ffd966;
        }

        .inline-message.error {
            color: #ffaeae;
            border-left: 3px solid #ff6b6b;
        }

        .footer-note {
            font-size: 0.7rem;
            text-align: center;
            margin-top: 25px;
            color: #9eaa8b;
        }

        /* Success animation for form */
        @keyframes glow {
            0% { box-shadow: 0 0 0 0 rgba(241, 196, 15, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(241, 196, 15, 0); }
            100% { box-shadow: 0 0 0 0 rgba(241, 196, 15, 0); }
        }
    </style>
</head>
<body>

<div class="register-card">
    <div class="banana-header">
        <span class="banana-icon">🍌</span>
        <h2>Jungle Vault</h2>
        <span class="banana-icon">🍌</span>
    </div>
    <div style="text-align: center;">
        <div class="subtitle">✨ Join the wildest banana race ✨</div>
    </div>

    <!-- Dynamic message area -->
    <?php if (!empty($inline_error)): ?>
    <div id="formFeedback" class="inline-message error">
        <?php echo $inline_error; ?>
    </div>
    <?php else: ?>
    <div id="formFeedback" style="min-height: 55px;"></div>
    <?php endif; ?>

    <form method="POST" id="registerForm">
        <div class="input-group">
            <span class="input-icon">🐒</span>
            <input type="text" name="username" id="username" placeholder="Chosen name" value="<?php echo $preserve_username; ?>" required autocomplete="off">
        </div>
        <div class="input-group">
            <span class="input-icon">🔐</span>
            <input type="password" name="password" id="password" placeholder="Secret code" required>
        </div>
        <button type="submit" class="create-btn">🌴 CREATE ACCOUNT 🌴</button>
    </form>

    <div class="action-links">
        <a href="login.php" class="action-btn">
         Login
        </a>
        <a href="index.php" class="action-btn menu-icon-btn">
         Menu
        </a>
    </div>
    <div class="footer-note">
        🍌 Every banana counts — start your journey today!
    </div>
</div>

<!-- Popup Modal (appears on successful account creation, stays until user clicks) -->
<div id="successModal" class="modal-overlay">
    <div class="success-popup">
        <div class="popup-banana">🍌✨🍌</div>
        <div class="popup-title">Account Created!</div>
        <div class="popup-message">
            Welcome to the jungle, brave adventurer!<br>
            Your banana journey starts now. 🐒
        </div>
        <a href="login.php" class="popup-login-btn">
            🔓 Click here to Login → 
        </a>
        <div>
            <button class="close-popup" id="closePopupBtn">✨ Stay on page ✨</button>
        </div>
    </div>
</div>

<script>
    // Trigger popup if account was successfully created
    const shouldOpenPopup = <?php echo $popup_trigger ? 'true' : 'false'; ?>;
    
    if (shouldOpenPopup) {
        // Clear any inline error messages
        const feedbackDiv = document.getElementById('formFeedback');
        if (feedbackDiv) {
            feedbackDiv.innerHTML = '';
            feedbackDiv.className = 'inline-message';
        }
        
        // Add a subtle glow animation to the form card
        const card = document.querySelector('.register-card');
        if (card) {
            card.style.animation = 'glow 0.8s ease-out';
            setTimeout(() => {
                if (card) card.style.animation = '';
            }, 800);
        }
        
        // Show modal
        const modal = document.getElementById('successModal');
        modal.classList.add('active');
        
        // Close popup handlers
        const closeBtn = document.getElementById('closePopupBtn');
        const closeModal = () => {
            modal.classList.remove('active');
        };
        
        closeBtn.addEventListener('click', closeModal);
        
        // Close when clicking on overlay backdrop
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });
        
        // Optional: Prevent closing on popup content click
        const popupContent = document.querySelector('.success-popup');
        if (popupContent) {
            popupContent.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        }
    }
    
    // Optional: Add real-time validation feedback
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const form = document.getElementById('registerForm');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            const username = usernameInput.value.trim();
            const password = passwordInput.value.trim();
            
            if (!username || !password) {
                e.preventDefault();
                const feedback = document.getElementById('formFeedback');
                feedback.innerHTML = '⚠️ Both fields are required to join the jungle!';
                feedback.className = 'inline-message error';
                feedback.style.display = 'block';
                return false;
            }
            return true;
        });
    }
</script>
</body>
</html>