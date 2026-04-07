<?php
session_start();

// 1. Check if user is logged in
$is_logged_in = isset($_SESSION['username']);
$current_user = $is_logged_in ? $_SESSION['username'] : "Guest";
$current_bananas = 0;

// 2. Fetch real data from database only if logged in
if ($is_logged_in) {
    // Connect using Port 3307
    $conn = new mysqli("localhost", "root", "", "banana_game");
    
    // Check connection
    if ($conn->connect_error) {
        // Log error instead of killing the page for the user
        error_log("Connection failed: " . $conn->connect_error);
    } else {
        // Use prepared statement to get banana count from leaderboard
        $stmt = $conn->prepare("SELECT bananas FROM leaderboard WHERE username = ?");
        $stmt->bind_param("s", $current_user);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $current_bananas = (int)$row['bananas'];
        }
        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banana Jungle - Home</title>
    
    <link rel="stylesheet" href="style.css">
</head>

<body>

<audio id="bgMusic" loop autoplay>
    <source src="assests/game_music.mp3" type="audio/mpeg">
</audio>

<script>
    (function() {
        const audio = document.getElementById('bgMusic');
        
        // Set volume
        audio.volume = 0.3;
        
        // Try to play immediately
        const playAudio = () => {
            audio.play().then(() => {
                console.log('🎵 Music playing successfully!');
            }).catch(error => {
                console.log('Autoplay blocked:', error);
                // Create a big play button as last resort
                createPlayButton();
            });
        };
        
        // Create floating play button if autoplay fails
        function createPlayButton() {
            const btn = document.createElement('div');
            btn.innerHTML = '🎵 Click to Enable Music 🎵';
            btn.style.cssText = `
                position: fixed;
                bottom: 30px;
                left: 50%;
                transform: translateX(-50%);
                background: rgba(0,0,0,0.9);
                color: #ffd700;
                padding: 12px 25px;
                border-radius: 50px;
                font-size: 16px;
                font-weight: bold;
                cursor: pointer;
                z-index: 10000;
                font-family: Arial;
                border: 2px solid #ffd700;
                box-shadow: 0 0 20px rgba(0,0,0,0.5);
                animation: pulse 1s infinite;
            `;
            
            // Add animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes pulse {
                    0% { transform: translateX(-50%) scale(1); }
                    50% { transform: translateX(-50%) scale(1.05); }
                    100% { transform: translateX(-50%) scale(1); }
                }
            `;
            document.head.appendChild(style);
            
            btn.onclick = () => {
                audio.play();
                btn.remove();
            };
            
            document.body.appendChild(btn);
            
            // Auto-hide after 10 seconds
            setTimeout(() => {
                if (btn.parentNode) btn.style.opacity = '0.5';
            }, 10000);
        }
        
        // Try to play when page loads
        window.addEventListener('load', playAudio);
        
        // Also try on any user interaction (for mobile)
        document.addEventListener('click', () => {
            if (audio.paused) {
                audio.play();
            }
        }, { once: true });
    })();
</script>

<header class="top-header">

    <div class="left-buttons">
        <?php if (!$is_logged_in): ?>
            <button class="header-btn" onclick="window.location.href='login.php'">Login</button>
        <?php else: ?>
            <button class="header-btn" onclick="window.location.href='logout.php'">Logout</button>
        <?php endif; ?>
        
        <button class="header-btn" onclick="window.location.href='leaderboard.php'">Leaderboard</button>
    </div>

    <!-- Attractive User Profile Box -->
    <div class="user-profile-card">
        <div class="profile-glow"></div>
        <div class="profile-content">
            <div class="avatar-container">
                <img src="assests/user.png" class="avatar-icon" alt="Monkey Avatar">
                <div class="avatar-badge"></div>
            </div>
            <div class="user-details">
                <span class="welcome-text">Welcome,</span>
                <span class="username-display"><?php echo htmlspecialchars($current_user); ?></span>
            </div>
            <div class="banana-stats">
                <img src="assests/banana.png" class="banana-icon" alt="Banana">
                <div class="banana-info">
                    <span class="banana-label">Bananas</span>
                    <span class="banana-amount"><?php echo $current_bananas; ?></span>
                </div>
            </div>
        </div>
    </div>

</header>

<video autoplay loop muted id="bgVideo" playsinline>
    <source src="assests/background.mp4" type="video/mp4">
</video>

<div class="game-container">
    <video autoplay loop muted class="game-video">
        <source src="assests/startgame.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>

    <button class="start-btn" onclick="startGame()">Start Game</button>
</div>

<script>
function startGame(){
    // Using the PHP variable inside JS
    const loggedIn = <?php echo json_encode($is_logged_in); ?>;
    
    if (!loggedIn) {
        alert("Please login to start your adventure!");
        window.location.href = "login.php";
    } else {
        window.location.href = "gamemap.php";
    }
}
</script>

<script src="script.js"></script>
</body>
</html>