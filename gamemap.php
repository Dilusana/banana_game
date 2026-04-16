<?php
session_start();

$is_logged_in = isset($_SESSION['username']);
$current_user = $is_logged_in ? $_SESSION['username'] : "Guest";
$current_bananas = 0;

if (isset($_COOKIE['user_info'])) {
    $user_info = json_decode($_COOKIE['user_info'], true);

    if ($user_info['ip'] === '::1') {
        $user_info['ip'] = '127.0.0.1';
    }
} else {
    $user_info = null;
}

if ($is_logged_in) {

$conn = new mysqli("localhost", "root", "", "banana_game");
    if (!$conn->connect_error) {

        $stmt = $conn->prepare("SELECT bananas FROM leaderboard WHERE username=?");
        $stmt->bind_param("s", $current_user);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $current_bananas = $row['bananas'];
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
    <title>Banana Jungle Game</title>
    <!-- Google Fonts Embed Code -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bungee&display=swap" rel="stylesheet">
    

<audio id="bgMusic" loop autoplay>
    <source src="assests/game_map.mp3" type="audio/mpeg">
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
    <style>
        body {
            margin: 0;
            height: 100vh;
            font-family: Segoe UI;
            background: black;
            overflow: hidden;
        }

        .game-container {
            position: relative;
            width: 100%;
            height: 100vh;
            overflow: hidden;
        }

        /* Background image that repeats horizontally for infinite scrolling */
        .game-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('assests/mapback.jpg');
            background-size: auto 100%;
            background-repeat: repeat-x;
            background-position: 0 0;
            background-color: #000;
            z-index: 1;
        }

        #user-info {
            position: absolute;
            top: -40px;
            left: 20px;
            z-index: 20;
            padding: 0px;
            min-width: 250px;
        }

        #score-display {
            position: absolute;
            top: 10px;
            left: 320px;
            z-index: 20;
            padding: 0px;
            min-width: 250px;
            color: #282501;
            font-weight: bold;
            font-size: 24px;
            margin-top: 5px;
            display: flex; 
            align-items: center; 
            gap: 5px; 
            font-family: 'Bungee', sans-serif; 
            font-weight: 400; 
            font-style: normal;
            font-size: 25px;
        }

        #current-level-display {
            position: absolute;
            top: 10px;
            left: 600px;
            z-index: 20;
            padding: 0px;
            min-width: 200px;
            color: #ff6b35;
            font-weight: bold;
            font-size: 24px;
            margin-top: 5px;
            display: flex; 
            align-items: center; 
            gap: 5px; 
            font-family: 'Bungee', sans-serif; 
            font-weight: 400; 
            font-style: normal;
            font-size: 25px;
            background: rgba(0,0,0,0.5);
            padding: 5px 15px;
            border-radius: 20px;
            border: 2px solid gold;
        }

        #hearts-display {
            position: absolute;
            top: 10px;
            right: 20px;
            z-index: 20;
            display: flex;
            gap: 10px;
            background: rgba(0,0,0,0.5);
            padding: 10px 20px;
            border-radius: 20px;
            border: 2px solid gold;
            font-family: 'Bungee', sans-serif;
        }

        .heart {
            font-size: 30px;
            transition: all 0.3s ease;
        }

        .heart-lost {
            opacity: 0.3;
            filter: grayscale(100%);
        }

        .heart-loss {
            animation: heartBreak 0.5s ease;
        }

        @keyframes heartBreak {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.3); opacity: 0.5; color: #ff0000; }
            100% { transform: scale(0); opacity: 0; }
        }

        .logout-btn {
            display: inline-block;
            margin-top: 12px;
            padding: 8px 16px;
            background: #e74c3c;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }

        .logout-btn:hover {
            background: #c0392b;
        }

        .menu-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 20;
            padding: 12px 22px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
        }

        .menu-btn:hover {
            background: #2980b9;
        }

        /* Levels container that scrolls with background */
        .levels-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 10;
            pointer-events: none;
            overflow: visible;
        }

        /* Level button styles */
        .level-btn {
            position: absolute;
            width: 70px;
            height: 70px;
            border-radius: 50%;
            border: 3px solid gold;
            background: #27ae60;
            color: white;
            font-size: 20px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: auto;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }

        .level-btn.completed {
            background: #95a5a6;
            border-color: #7f8c8d;
            cursor: default;
            opacity: 0.6;
        }

        .level-btn.current {
            background: #e67e22;
            border-color: #f39c12;
            animation: pulse 1s infinite;
        }

        .level-btn.locked {
            background: #444;
            border-color: #666;
            cursor: not-allowed;
            opacity: 0.5;
        }

        .level-btn:hover:not(.completed):not(.locked) {
            transform: scale(1.1);
            background: gold;
            color: black;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .puzzle-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            display: none;
            z-index: 100;
            width: 650px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.6);
        }

        #timer {
            font-size: 26px;
            color: red;
            font-weight: bold;
            margin-bottom: 15px;
        }

        #question-area img {
            width: 420px;
            max-width: 100%;
            margin-bottom: 20px;
        }

        .choices-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin: 20px 0;
        }

        .choice-btn {
            padding: 12px 20px;
            font-size: 18px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
            font-weight: bold;
        }

        .choice-btn:hover {
            background: #2980b9;
            transform: scale(1.05);
        }

        .choice-btn.selected {
            background: #27ae60;
            box-shadow: 0 0 10px rgba(39, 174, 96, 0.5);
        }

        input {
            width: 85%;
            padding: 14px;
            font-size: 20px;
            margin-top: 15px;
            border-radius: 8px;
            border: 1px solid #ccc;
            display: none;
        }

        .submit-btn {
            margin-top: 15px;
            padding: 12px 28px;
            background: #27ae60;
            border: none;
            color: white;
            font-size: 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
        }

        .submit-btn:hover {
            background: #219a52;
            transform: scale(1.05);
        }

        .close-btn {
            position: absolute;
            top: 10px;
            right: 18px;
            font-size: 28px;
            cursor: pointer;
        }

        #monkey {
            position: absolute;
            bottom: 60px;
            left: 100px;
            width: 80px;
            height: auto;
            z-index: 15;
            transition: left 0.05s linear, bottom 0.3s ease;
        }

        .victory-message {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: linear-gradient(135deg, gold, orange);
            color: white;
            padding: 30px 50px;
            border-radius: 20px;
            font-size: 32px;
            font-weight: bold;
            text-align: center;
            z-index: 200;
            display: none;
            box-shadow: 0 0 50px rgba(0, 0, 0, 0.5);
            animation: bounce 0.5s ease;
        }

        .death-message {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: linear-gradient(135deg, #8B0000, #DC143C);
            color: white;
            padding: 30px 50px;
            border-radius: 20px;
            font-size: 28px;
            font-weight: bold;
            text-align: center;
            z-index: 200;
            display: none;
            box-shadow: 0 0 50px rgba(0, 0, 0, 0.5);
            animation: shake 0.5s ease;
            min-width: 400px;
        }

        .death-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 25px;
        }

        .death-btn {
            padding: 12px 24px;
            font-size: 18px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .restart-btn {
            background: #27ae60;
            color: white;
        }

        .restart-btn:hover {
            background: #219a52;
            transform: scale(1.05);
        }

        .menu-death-btn {
            background: #34db45;
            color: white;
        }

        .menu-death-btn:hover {
            background: #2980b9;
            transform: scale(1.05);
        }

        @keyframes bounce {
            0%, 100% { transform: translate(-50%, -50%) scale(1); }
            50% { transform: translate(-50%, -50%) scale(1.1); }
        }

        @keyframes shake {
            0%, 100% { transform: translate(-50%, -50%) translateX(0); }
            25% { transform: translate(-50%, -50%) translateX(-10px); }
            75% { transform: translate(-50%, -50%) translateX(10px); }
        }

        .reset-btn {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 20;
            padding: 12px 24px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            display: none;
        }

        .reset-btn:hover {
            background: #2980b9;
        }

        .controls-hint {
            position: absolute;
            bottom: 20px;
            right: 20px;
            z-index: 20;
            background: rgba(0, 0, 0, 0.7);
            padding: 10px 15px;
            border-radius: 8px;
            color: white;
            font-size: 14px;
            font-family: monospace;
            border: 1px solid gold;
        }

        .controls-hint span {
            color: gold;
            font-weight: bold;
        }

        .answer-mode-toggle {
            margin-bottom: 15px;
        }

        .mode-btn {
            padding: 8px 16px;
            margin: 0 5px;
            background: #95a5a6;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .mode-btn.active {
            background: #27ae60;
        }
        
        .saving-indicator {
            position: fixed;
            bottom: 20px;
            left: 20px;
            background: rgba(0,0,0,0.8);
            color: #27ae60;
            padding: 8px 15px;
            border-radius: 8px;
            font-size: 12px;
            z-index: 1000;
            display: none;
            font-family: monospace;
            border: 1px solid #27ae60;
        }

        /* Fixed notification message styles - no black box */
        .notification-message {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 20px 40px;
            border-radius: 15px;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            z-index: 1000;
            animation: slideInOut 2s ease forwards;
            pointer-events: none;
            box-shadow: 0 0 20px rgba(0,0,0,0.3);
        }
        .header-btn {
    width: 140px;        
    height: 45px;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
    border-radius: 25px;
    border: 2px solid #f1c40f;
    background: linear-gradient(135deg, #333, #222);
    color: white;
    font-weight: bold;
    display: flex;
    justify-content: center;
    align-items: center;
    backdrop-filter: blur(5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}
        @keyframes slideInOut {
            0% {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.8);
            }
            15% {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
            85% {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
            100% {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.8);
                visibility: hidden;
            }
        }
    </style>
</head>

<body>
    <div class="game-container" id="gameContainer">
        <div class="game-background" id="gameBackground"></div>
        
        <img id="monkey" src="assests/Idle__000.png">
        
        <div id="user-info" style="display:flex; align-items:center; gap:5px; font-family: 'Bungee', sans-serif; font-weight: 400; font-style: normal;font-size: 25px;">
            <img src="assests/user.png" alt="User Icon" style="width:100px; height:150px; display:block; margin:0; padding:0;"> 
            <span>Player: <strong><?php echo htmlspecialchars($current_user); ?></strong></span>
        </div>

        <div id="score-display">
            🍌 Bananas: <span id="banana-count"><?php echo $current_bananas; ?></span>
        </div>

        <div id="current-level-display">
            📍 Current Level: <span id="current-level"><?php echo $current_bananas + 1; ?></span>
        </div>

        <div id="hearts-display">
            <span style="color: gold; margin-right: 10px;">❤️ LIVES:</span>
            <span id="heart1" class="heart">❤️</span>
            <span id="heart2" class="heart">❤️</span>
            <span id="heart3" class="heart">❤️</span>
        </div>

        <a href="index.php" style="position:relative; display:inline-block; width:160px;">
            <img src="assests/button.png" alt="Menu" style="width:100%; height:auto; display:block;">
            <span style="position:absolute; top:150px; left:150px; transform:translate(-50%, -50%); color:white; font-weight:bold; font-size:30px; pointer-events:none;">
                Menu
            </span>
        </a>

        <div class="levels-container" id="levelsContainer">
            <?php
            // Generate level buttons 1-50 with fixed positions relative to world
            for ($i = 1; $i <= 50; $i++) {
                $status = '';
                if ($i < $current_bananas + 1) {
                    $status = 'completed';
                } elseif ($i == $current_bananas + 1) {
                    $status = 'current';
                } else {
                    $status = 'locked';
                }
                echo "<button class='level-btn lvl-$i $status' data-level='$i' data-world-x='" . ($i * 200) . "' style='left: " . ($i * 200) . "px; top: 70%;' onclick='handleLevelClick($i)'>$i</button>";
            }
            ?>
        </div>

        <div class="controls-hint">
            <a href="index.php" class="header-btn"> Menu</a>


        </div>

        <div id="puzzleBox" class="puzzle-overlay">
            <span class="close-btn" onclick="closePuzzle()">×</span>
            <h2 id="level-title"></h2>
            <div id="timer"></div>
            <div id="question-area"></div>
            
            <div class="answer-mode-toggle">
                <button class="mode-btn" id="multipleChoiceMode" onclick="setAnswerMode('multiple')">Multiple Choice</button>
            </div>
            
            <div class="choices-container" id="choicesContainer"></div>
            <input type="number" id="user-answer" placeholder="Enter your answer">
            <button class="submit-btn" onclick="checkAnswer()">Submit Answer</button>
        </div>

        <div id="victoryMessage" class="victory-message"></div>
        <div id="deathMessage" class="death-message">
            💀 GAME OVER! 💀<br>You lost all your hearts!<br>
            <div class="death-buttons">
                <button class="header-btn" onclick="resetGame()">Play Again</button>
                <a href="index.php" class="header-btn"> Menu</a>
            </div>
        </div>
        <button id="resetBtn" class="reset-btn" onclick="resetGame()">🔄 Start Over</button>
        <div id="savingIndicator" class="saving-indicator">💾 Saving...</div>
    </div>

    <script>
    // ============================================
    // GAME STATE VARIABLES
    // ============================================
    const isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
    let bananaScore = <?php echo $current_bananas; ?>;
    let correctAnswer = null;
    let activeLevel = 1;
    let timer;
    let timeLeft;
    let isDead = false;
    let isGameActive = true;
    let isPuzzleOpen = false;
    let puzzleTriggeredForLevel = false;
    let currentAnswerMode = 'multiple';
    let currentChoices = [];
    let selectedChoice = null;

    // Heart system variables
    let currentHearts = 3;
    let wrongAnswersForCurrentLevel = 0;

    // Animation state variables
    let isMoving = false;
    let isJumping = false;
    
    // Keyboard control variables
    let isMovingRight = false;
    let moveInterval = null;
    let jumpCooldown = false;
    
    // World scroll position
    let worldScrollX = 0;
    let cameraX = 0;
    
    // Character position in world coordinates
    let characterWorldX = 100;
    
    // Constants
    const imageWidth = 1920;
    const levelSpacing = 200;

    // ============================================
    // NOTIFICATION FUNCTIONS (Fixed - No black box)
    // ============================================
    
    function showNotification(message, color, bgColor = 'rgba(0,0,0,0.9)') {
        // Remove any existing notifications
        const existingNotifications = document.querySelectorAll('.notification-message');
        existingNotifications.forEach(notif => notif.remove());
        
        const notificationDiv = document.createElement('div');
        notificationDiv.className = 'notification-message';
        notificationDiv.textContent = message;
        notificationDiv.style.backgroundColor = bgColor;
        notificationDiv.style.color = color;
        notificationDiv.style.border = `2px solid ${color}`;
        document.body.appendChild(notificationDiv);
        
        setTimeout(() => {
            if (notificationDiv && notificationDiv.remove) {
                notificationDiv.remove();
            }
        }, 2000);
    }

    function showSuccessMessage(message) {
        showNotification(message, '#27ae60', 'rgba(0,0,0,0.85)');
    }

    function showErrorMessage(message) {
        showNotification(message, '#e74c3c', 'rgba(0,0,0,0.85)');
    }

    function showHeartMessage(message) {
        showNotification(message, '#ff6b35', 'rgba(0,0,0,0.85)');
    }

    // ============================================
    // HEART SYSTEM FUNCTIONS
    // ============================================
    
    function updateHeartsDisplay() {
        for (let i = 1; i <= 3; i++) {
            const heart = document.getElementById(`heart${i}`);
            if (heart) {
                if (i <= currentHearts) {
                    heart.style.opacity = "1";
                    heart.style.filter = "none";
                } else {
                    heart.style.opacity = "0.3";
                    heart.style.filter = "grayscale(100%)";
                }
            }
        }
    }

    function loseHeart() {
    if (currentHearts > 0) {
        currentHearts--;
        
        // Animate the lost heart
        const heartToLose = document.getElementById(`heart${currentHearts + 1}`);
        if (heartToLose) {
            heartToLose.classList.add('heart-loss');
            setTimeout(() => {
                heartToLose.classList.remove('heart-loss');
            }, 500);
        }
        
        updateHeartsDisplay();
        
        // Show heart loss message
        showHeartMessage(`💔 You lost a heart! ${currentHearts} lives remaining! 💔`);
        
        // If no hearts left, trigger DEATH SEQUENCE (not immediate reset)
        if (currentHearts === 0) {
            startDeathAnimation();  // Call death animation instead of direct reset
            return true;
        }
        return false;
    }
    return true;
}


    function resetHearts() {
        currentHearts = 3;
        wrongAnswersForCurrentLevel = 0;
        updateHeartsDisplay();
    }

    // ============================================
    // SAVE SCORE FUNCTION
    // ============================================
    
    function showSavingIndicator(message) {
        const indicator = document.getElementById('savingIndicator');
        indicator.textContent = message;
        indicator.style.display = 'block';
        setTimeout(() => {
            indicator.style.display = 'none';
        }, 2000);
    }
    
    function saveScoreToDB(score) {
        if (!isLoggedIn) {
            console.log("User not logged in, skipping save");
            return;
        }
        
        console.log("Saving score to database:", score);
        showSavingIndicator("💾 Saving score: " + score);
        
        fetch("save_score.php", {
            method: "POST",
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            body: "score=" + score
        })
        .then(response => response.text())
        .then(data => {
            console.log("Server response:", data);
            if (data.includes("success") || data.includes("updated") || data.includes("inserted")) {
                showSavingIndicator("✅ Score saved: " + score);
            } else {
                showSavingIndicator("⚠️ " + data.substring(0, 50));
            }
        })
        .catch(error => {
            console.error("Error saving score:", error);
            showSavingIndicator("❌ Failed to save!");
        });
    }

    // ============================================
    // ANIMATION FRAMES
    // ============================================
    let walkFrames = [
        "assests/Run__000.png", "assests/Run__001.png", "assests/Run__002.png",
        "assests/Run__003.png", "assests/Run__004.png", "assests/Run__005.png",
        "assests/Run__006.png", "assests/Run__007.png"
    ];

    let jumpFrames = [
        "assests/Jump__000.png", "assests/Jump__001.png", "assests/Jump__002.png",
        "assests/Jump__003.png", "assests/Jump__004.png", "assests/Jump__005.png",
        "assests/Jump__006.png", "assests/Jump__007.png"
    ];

    let idleFrames = [
        "assests/Idle__000.png", "assests/Idle__001.png", "assests/Idle__002.png",
        "assests/Idle__003.png", "assests/Idle__004.png", "assests/Idle__005.png",
        "assests/Idle__006.png", "assests/Idle__007.png", "assests/Idle__008.png",
        "assests/Idle__009.png"
    ];

    let deathFrames = [
        "assests/Dead__000.png", "assests/Dead__001.png", "assests/Dead__002.png",
        "assests/Dead__003.png", "assests/Dead__004.png", "assests/Dead__005.png",
        "assests/Dead__006.png", "assests/Dead__007.png", "assests/Dead__008.png",
        "assests/Dead__009.png"
    ];

    let idleIndex = 0, walkIndex = 0, jumpIndex = 0, deathIndex = 0;
    let idleInterval, walkInterval, jumpInterval, deathInterval;

    // ============================================
    // ANSWER MODE FUNCTIONS
    // ============================================
    
    function setAnswerMode(mode) {
        currentAnswerMode = mode;
        
        document.getElementById('multipleChoiceMode').classList.remove('active');
        document.getElementById(`${mode}ChoiceMode`).classList.add('active');
        
        if (mode === 'multiple') {
            document.getElementById('choicesContainer').style.display = 'grid';
            document.getElementById('user-answer').style.display = 'none';
        } else {
            document.getElementById('choicesContainer').style.display = 'none';
            document.getElementById('user-answer').style.display = 'block';
        }
    }
    
    function generateMultipleChoiceAnswers(correct) {
        let choices = [correct];
        
        while (choices.length < 4) {
            let offset = Math.floor(Math.random() * 20) - 10;
            let distractor = correct + offset;
            
            if (distractor !== correct && !choices.includes(distractor) && distractor >= 0) {
                choices.push(distractor);
            }
        }
        
        for (let i = choices.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [choices[i], choices[j]] = [choices[j], choices[i]];
        }
        
        return choices;
    }
    
    function displayMultipleChoice() {
        const container = document.getElementById('choicesContainer');
        container.innerHTML = '';
        selectedChoice = null;
        
        currentChoices.forEach((choice, index) => {
            const btn = document.createElement('button');
            btn.className = 'choice-btn';
            btn.textContent = choice;
            btn.onclick = () => selectChoice(choice, btn);
            container.appendChild(btn);
        });
    }
    
    function selectChoice(choice, btnElement) {
        document.querySelectorAll('.choice-btn').forEach(btn => {
            btn.classList.remove('selected');
        });
        
        btnElement.classList.add('selected');
        selectedChoice = choice;
    }

    // ============================================
    // WORLD SCROLLING FUNCTIONS
    // ============================================

    function updateWorldScroll(deltaX) {
        worldScrollX += deltaX;
        cameraX = worldScrollX;
        
        const background = document.getElementById("gameBackground");
        let bgScroll = worldScrollX % imageWidth;
        background.style.backgroundPosition = `-${bgScroll}px 0`;
        
        const levelBtns = document.querySelectorAll('.level-btn');
        levelBtns.forEach(btn => {
            const worldX = parseFloat(btn.getAttribute('data-world-x'));
            const screenX = worldX - worldScrollX;
            btn.style.left = screenX + "px";
            
            if (screenX < -200 || screenX > window.innerWidth + 200) {
                btn.style.display = 'none';
            } else {
                btn.style.display = 'flex';
            }
        });
    }

    function resetWorldScroll() {
        worldScrollX = 0;
        cameraX = 0;
        updateWorldScroll(0);
    }

    // ============================================
    // LEVEL BUTTON MANAGEMENT
    // ============================================

    function updateLevelButtons() {
        for (let i = 1; i <= 50; i++) {
            const btn = document.querySelector(`.lvl-${i}`);
            if (btn) {
                btn.classList.remove('completed', 'current', 'locked');
                if (i < bananaScore + 1) {
                    btn.classList.add('completed');
                } else if (i === bananaScore + 1) {
                    btn.classList.add('current');
                } else {
                    btn.classList.add('locked');
                }
            }
        }
        document.getElementById('current-level').innerText = bananaScore + 1;
        puzzleTriggeredForLevel = false;
    }

    function positionCharacterAtCurrentLevel() {
        const currentLevel = bananaScore + 1;
        if (currentLevel > 50) {
            const lastLevelWorldX = 50 * 200;
            characterWorldX = lastLevelWorldX - 100;
            const targetScrollX = characterWorldX - 100;
            worldScrollX = targetScrollX;
            updateWorldScroll(0);
            return;
        }
        
        const currentLevelWorldX = currentLevel * 200;
        characterWorldX = currentLevelWorldX - 100;
        const targetScrollX = characterWorldX - 100;
        worldScrollX = targetScrollX;
        updateWorldScroll(0);
        
        const monkey = document.getElementById("monkey");
        monkey.style.left = "100px";
        monkey.style.bottom = "60px";
        
        puzzleTriggeredForLevel = false;
        wrongAnswersForCurrentLevel = 0;
    }

    // ============================================
    // PUZZLE AUTO-TRIGGER
    // ============================================
    
    function checkLevelProximity() {
        if (isPuzzleOpen || isDead || !isGameActive) return;
        
        const currentLevel = bananaScore + 1;
        if (currentLevel > 50) return;
        
        const levelBtn = document.querySelector(`.lvl-${currentLevel}`);
        if (!levelBtn) return;
        
        const btnScreenX = parseFloat(levelBtn.style.left);
        const monkeyScreenX = parseFloat(document.getElementById("monkey").style.left);
        const distance = Math.abs(btnScreenX - monkeyScreenX);
        const touchThreshold = 70;
        
        if (distance < touchThreshold && !levelBtn.classList.contains('completed') && !puzzleTriggeredForLevel) {
            puzzleTriggeredForLevel = true;
            activeLevel = currentLevel;
            
            if (isMovingRight) {
                stopMoving();
            }
            
            openPuzzle();
        }
    }

    // ============================================
    // KEYBOARD CONTROLS
    // ============================================
    
    function startMovingRight() {
        if (isDead || !isGameActive || isMovingRight || isJumping || isPuzzleOpen) return;
        
        isMovingRight = true;
        isMoving = true;
        startWalkAnimation();
        
        moveInterval = setInterval(() => {
            if (isDead || !isGameActive || !isMovingRight || isPuzzleOpen) {
                stopMoving();
                return;
            }
            
            characterWorldX += 8;
            const targetScrollX = characterWorldX - 100;
            const scrollDiff = targetScrollX - worldScrollX;
            
            if (Math.abs(scrollDiff) > 1) {
                const scrollStep = scrollDiff * 0.1;
                updateWorldScroll(scrollStep);
            }
            
            document.getElementById("monkey").style.left = "100px";
            checkLevelProximity();
        }, 30);
    }

    function stopMoving() {
        if (moveInterval) {
            clearInterval(moveInterval);
            moveInterval = null;
        }
        isMovingRight = false;
        isMoving = false;
        if (!isJumping && !isDead && isGameActive && !isPuzzleOpen) {
            startIdleAnimation();
        }
    }

    function jump() {
        if (isDead || !isGameActive || isJumping || jumpCooldown || isPuzzleOpen) return;
        
        jumpCooldown = true;
        isJumping = true;
        
        if (isMovingRight) {
            stopMoving();
        }
        
        const monkey = document.getElementById("monkey");
        const originalBottom = 60;
        let jumpHeight = 0;
        let goingUp = true;
        
        startJumpAnimation();
        
        const jumpPhysicsInterval = setInterval(() => {
            if (!isJumping || isDead) {
                clearInterval(jumpPhysicsInterval);
                monkey.style.bottom = originalBottom + "px";
                if (!isDead && isGameActive && !isPuzzleOpen) {
                    startIdleAnimation();
                }
                setTimeout(() => { jumpCooldown = false; }, 500);
                return;
            }
            
            if (goingUp) {
                jumpHeight += 15;
                monkey.style.bottom = (originalBottom + jumpHeight) + "px";
                if (jumpHeight >= 80) goingUp = false;
            } else {
                jumpHeight -= 15;
                monkey.style.bottom = (originalBottom + jumpHeight) + "px";
                if (jumpHeight <= 0) {
                    clearInterval(jumpPhysicsInterval);
                    isJumping = false;
                    monkey.style.bottom = originalBottom + "px";
                    if (!isDead && isGameActive && !isPuzzleOpen) {
                        startIdleAnimation();
                    }
                    setTimeout(() => { jumpCooldown = false; }, 500);
                }
            }
        }, 30);
    }

    function handleKeyDown(e) {
        if (isDead || !isGameActive) return;
        
        if (isPuzzleOpen) {
            if (e.key === 'ArrowRight' || e.key === 'ArrowUp' || e.key === 'Enter') {
                e.preventDefault();
            }
            if (e.key === 'Enter') {
                checkAnswer();
            }
            return;
        }
        
        switch(e.key) {
            case 'ArrowRight':
                e.preventDefault();
                startMovingRight();
                break;
            case 'ArrowUp':
                e.preventDefault();
                jump();
                break;
        }
    }

    function handleKeyUp(e) {
        switch(e.key) {
            case 'ArrowRight':
                e.preventDefault();
                stopMoving();
                break;
        }
    }

    // ============================================
    // ANIMATION FUNCTIONS
    // ============================================

    function startIdleAnimation() {
        if (isDead) return;
        stopAllAnimations();
        idleInterval = setInterval(() => {
            if (!isMoving && !isJumping && !isDead && isGameActive && !isPuzzleOpen) {
                document.getElementById("monkey").src = idleFrames[idleIndex];
                idleIndex = (idleIndex + 1) % idleFrames.length;
            }
        }, 120);
    }

    function startWalkAnimation() {
        if (isDead) return;
        stopAllAnimations();
        walkInterval = setInterval(() => {
            if (isMoving && !isJumping && !isDead && isGameActive && !isPuzzleOpen) {
                document.getElementById("monkey").src = walkFrames[walkIndex];
                walkIndex = (walkIndex + 1) % walkFrames.length;
            }
        }, 100);
    }

    function startJumpAnimation() {
        if (isDead) return;
        stopAllAnimations();
        jumpInterval = setInterval(() => {
            if (isJumping && !isDead && isGameActive) {
                document.getElementById("monkey").src = jumpFrames[jumpIndex];
                jumpIndex = (jumpIndex + 1) % jumpFrames.length;
            }
        }, 80);
    }

    function startDeathAnimation() {
        isDead = true;
        isGameActive = false;
        stopMoving();
        stopAllAnimations();

        const monkey = document.getElementById("monkey");
        deathIndex = 0;

        deathInterval = setInterval(() => {
            if (deathIndex < deathFrames.length) {
                monkey.src = deathFrames[deathIndex];
                deathIndex++;
            } else {
                clearInterval(deathInterval);
                showDeathMessage();
            }
        }, 100);
    }

    function showDeathMessage() {
        const deathDiv = document.getElementById("deathMessage");
        deathDiv.style.display = "block";
        
        const levelBtns = document.querySelectorAll('.level-btn');
        levelBtns.forEach(btn => btn.style.pointerEvents = 'none');
    }

    function stopAllAnimations() {
        if (idleInterval) clearInterval(idleInterval);
        if (walkInterval) clearInterval(walkInterval);
        if (jumpInterval) clearInterval(jumpInterval);
        if (deathInterval) clearInterval(deathInterval);
    }

    // ============================================
    // PUZZLE FUNCTIONS
    // ============================================

    function startTimer(level) {
        if (level < 10) timeLeft = 60;
        else if (level < 20) timeLeft = 45;
        else if (level < 35) timeLeft = 30;
        else timeLeft = 20;
        
        clearInterval(timer);
        timer = setInterval(function() {
            if (!isDead && isGameActive) {
                document.getElementById("timer").innerText = "⏱ " + timeLeft + " sec";
                timeLeft--;
                if (timeLeft < 0) {
                    clearInterval(timer);
                    closePuzzle();
                    loseHeart();
                    if (currentHearts > 0) {
                        setTimeout(() => {
                            if (!isDead && currentHearts > 0) {
                                openPuzzle();
                            }
                        }, 1000);
                    }
                }
            }
        }, 1000);
    }

    async function openPuzzle() {
        if (isDead || isPuzzleOpen || currentHearts === 0) return;
        
        isPuzzleOpen = true;
        stopMoving();
        
        const box = document.getElementById("puzzleBox");
        const area = document.getElementById("question-area");
        document.getElementById("level-title").innerText = "Level " + activeLevel;
        document.getElementById("user-answer").value = "";
        box.style.display = "block";
        area.innerHTML = "Loading puzzle...";
        startTimer(activeLevel);
        
        try {
            const response = await fetch("https://marcconrad.com/uob/banana/api.php");
            const data = await response.json();
            correctAnswer = data.solution;
            area.innerHTML = `<img src="${data.question}" alt="Puzzle Question">`;
            
            currentChoices = generateMultipleChoiceAnswers(correctAnswer);
            displayMultipleChoice();
            setAnswerMode('multiple');
            
        } catch {
            area.innerHTML = "Puzzle loading failed. Please try again!";
        }
    }

    function checkAnswer() {
        if (isDead) {
            showErrorMessage("You are dead! Please reset the game to continue!");
            closePuzzle();
            return;
        }
        
        let userAnswer;
        
        if (currentAnswerMode === 'multiple') {
            if (selectedChoice === null) {
                showErrorMessage("Please select an answer!");
                return;
            }
            userAnswer = selectedChoice;
        } else {
            userAnswer = parseInt(document.getElementById("user-answer").value);
            if (isNaN(userAnswer)) {
                showErrorMessage("Please enter a valid number!");
                return;
            }
        }
        
        if (userAnswer == correctAnswer) {
            // Correct answer
            wrongAnswersForCurrentLevel = 0;
            clearInterval(timer);
            
            bananaScore++;
            document.getElementById("banana-count").innerText = bananaScore;
            document.getElementById("current-level").innerText = bananaScore + 1;
            
            // Show success message
            showSuccessMessage(`✅ CORRECT! 🍌 +1 Banana! 🍌 (Total: ${bananaScore}) ✅`);
            
            if (bananaScore === 50) {
                showVictoryMessage();
            }
            
            saveScoreToDB(bananaScore);
            updateLevelButtons();
            positionCharacterAtCurrentLevel();
            
            closePuzzle();
            isPuzzleOpen = false;
            startIdleAnimation();
            
            // Bonus heart if below max
            if (currentHearts < 3) {
                currentHearts++;
                updateHeartsDisplay();
                showHeartMessage("❤️ BONUS! You gained a heart! ❤️");
            }
        } else {
            // Wrong answer
            wrongAnswersForCurrentLevel++;
            showErrorMessage(`❌ WRONG! The correct answer was ${correctAnswer}. ${wrongAnswersForCurrentLevel} attempt(s) used. ❌`);
            
            const gameOver = loseHeart();
            clearInterval(timer);
            closePuzzle();
            
            if (!gameOver && currentHearts > 0) {
                puzzleTriggeredForLevel = false;
                setTimeout(() => {
                    if (!isDead && isGameActive && currentHearts > 0) {
                        showHeartMessage(`🔄 Retrying Level ${activeLevel}... You have ${currentHearts} hearts left! 🔄`);
                        setTimeout(() => {
                            if (!isPuzzleOpen && !isDead && currentHearts > 0) {
                                openPuzzle();
                            }
                        }, 1500);
                    }
                }, 1000);
            }
        }
    }

    function showVictoryMessage() {
        const victoryDiv = document.getElementById("victoryMessage");
        victoryDiv.innerHTML = "🎉🏆 VICTORY! 🏆🎉<br>You've completed all 50 levels!<br>🍌🍌🍌🍌🍌";
        victoryDiv.style.display = "block";
        setTimeout(() => { victoryDiv.style.display = "none"; }, 5000);
    }

    function closePuzzle() {
        document.getElementById("puzzleBox").style.display = "none";
        clearInterval(timer);
        isPuzzleOpen = false;
        selectedChoice = null;
        if (!isDead && isGameActive) {
            startIdleAnimation();
        }
    }

    function handleLevelClick(level) {
        if (isDead) {
            showErrorMessage("You are dead! Click 'Play Again' to restart!");
            return;
        }
        if (!isLoggedIn) {
            showErrorMessage("Please login to play");
            setTimeout(() => {
                window.location.href = "login.php";
            }, 1500);
            return;
        }
        if (level > bananaScore + 1) {
            showErrorMessage("Complete Level " + (bananaScore + 1) + " first to unlock Level " + level + "! 🍌");
            return;
        }
        if (level < bananaScore + 1) {
            showErrorMessage("You've already completed Level " + level + "!");
            return;
        }
        
        activeLevel = level;
        openPuzzle();
    }

    function resetGame() {
        
            isDead = false;
            isGameActive = true;
            isPuzzleOpen = false;
            puzzleTriggeredForLevel = false;
            selectedChoice = null;
            
            // Reset hearts
            currentHearts = 3;
            wrongAnswersForCurrentLevel = 0;
            updateHeartsDisplay();
            
            if (timer) clearInterval(timer);
            
            stopMoving();
            stopAllAnimations();
            
            bananaScore = 0;
            document.getElementById("banana-count").innerText = bananaScore;
            document.getElementById("current-level").innerText = bananaScore + 1;
            
            saveScoreToDB(bananaScore);
            
            for (let i = 1; i <= 50; i++) {
                const btn = document.querySelector(`.lvl-${i}`);
                if (btn) {
                    btn.classList.remove('completed', 'current', 'locked');
                    if (i === 1) btn.classList.add('current');
                    else btn.classList.add('locked');
                }
            }
            
            document.getElementById("deathMessage").style.display = "none";
            
            const levelBtns = document.querySelectorAll('.level-btn');
            levelBtns.forEach(btn => btn.style.pointerEvents = 'auto');
            
            const firstLevelWorldX = 1 * 200;
            characterWorldX = firstLevelWorldX - 100;
            const targetScrollX = characterWorldX - 100;
            worldScrollX = targetScrollX;
            updateWorldScroll(0);
            
            const monkey = document.getElementById("monkey");
            monkey.src = "assests/Idle__000.png";
            monkey.style.left = "100px";
            monkey.style.bottom = "60px";
            
            activeLevel = 1;
            closePuzzle();
            startIdleAnimation();
            
            showSuccessMessage("🔄 GAME RESET! Starting from Level 1 with 3 Hearts! 🔄");
        
    }

    // ============================================
    // INITIALIZATION
    // ============================================
    
    document.addEventListener('keydown', handleKeyDown);
    document.addEventListener('keyup', handleKeyUp);
    
    startIdleAnimation();
    updateHeartsDisplay();
    
    const monkey = document.getElementById("monkey");
    monkey.style.left = "100px";
    monkey.style.bottom = "60px";
    
    const background = document.getElementById("gameBackground");
    background.style.backgroundSize = "auto 100%";
    background.style.backgroundRepeat = "repeat-x";
    background.style.backgroundPosition = "0 0";
    background.style.backgroundColor = "#000";
    
    updateWorldScroll(0);
    updateLevelButtons();
    positionCharacterAtCurrentLevel();
    
    document.getElementById("banana-count").innerText = bananaScore;
    
    setInterval(() => {
        if (!isPuzzleOpen && !isDead && isGameActive) {
            checkLevelProximity();
        }
    }, 50);
    
    console.log("Game initialized. Score:", bananaScore, "Hearts:", currentHearts);
    </script>
</body>
</html>
