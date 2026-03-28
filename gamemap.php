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
        }

        video {
            position: absolute;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 1;
        }

        #user-info {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 20;
            background: rgba(0, 0, 0, 0.85);
            padding: 18px;
            border-radius: 12px;
            color: white;
            border: 2px solid gold;
            min-width: 220px;
        }

        #score-display {
            color: gold;
            font-weight: bold;
            font-size: 24px;
            margin-top: 5px;
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

        .level-btn {
            position: absolute;
            width: 65px;
            height: 65px;
            border-radius: 50%;
            border: 4px solid gold;
            background: #444;
            color: white;
            font-size: 25px;
            font-weight: bold;
            cursor: pointer;
            z-index: 10;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Style for unlocked levels */
        .level-btn.unlocked {
            background: #27ae60;
            border-color: #f1c40f;
            box-shadow: 0 0 15px rgba(39, 174, 96, 0.5);
        }

        /* Style for completed levels */
        .level-btn.completed {
            background: #95a5a6;
            border-color: #7f8c8d;
            cursor: default;
            opacity: 0.6;
        }

        /* Style for current level */
        .level-btn.current {
            background: #e67e22;
            border-color: #f39c12;
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }

        .level-btn:hover:not(.completed) {
            transform: scale(1.2);
            background: gold;
            color: black;
        }

        .level-btn.completed:hover {
            transform: scale(1);
            background: #95a5a6;
            cursor: default;
        }

        /* Level button positions - adjusted to be next to the buttons */
        .lvl-1 {
            top: 70%;
            left: 13%;
        }

        .lvl-2 {
            top: 60%;
            left: 13%;
        }

        .lvl-3 {
            top: 50%;
            left: 13%;
        }

        .lvl-4 {
            top: 40%;
            left: 13%;
        }

        .lvl-5 {
            top: 30%;
            left: 13%;
        }

        .lvl-6 {
            top: 20%;
            left: 15%;
        }

        .lvl-7 {
            top: 18%;
            left: 20%;
        }

        .lvl-8 {
            top: 18%;
            left: 25%;
        }

        .lvl-9 {
            top: 18%;
            left: 30%;
        }

        .lvl-10 {
            top: 18%;
            left: 35%;
        }

        .lvl-11 {
            top: 18%;
            left: 40%;
        }

        .lvl-12 {
            top: 18%;
            left: 45%;
        }

        .lvl-13 {
            top: 18%;
            left: 50%;
        }

        .lvl-14 {
            top: 18%;
            left: 55%;
        }

        .lvl-15 {
            top: 18%;
            left: 60%;
        }

        .lvl-16 {
            top: 18%;
            left: 65%;
        }

        .lvl-17 {
            top: 18%;
            left: 70%;
        }

        .lvl-18 {
            top: 18%;
            left: 75%;
        }

        .lvl-19 {
            top: 22%;
            left: 80%;
        }

        .lvl-20 {
            top: 32%;
            left: 83%;
        }

        .lvl-21 {
            top: 42%;
            left: 83%;
        }

        .lvl-22 {
            top: 52%;
            left: 83%;
        }

        .lvl-23 {
            top: 62%;
            left: 83%;
        }

        .lvl-24 {
            top: 72%;
            left: 82%;
        }

        .lvl-25 {
            top: 80%;
            left: 78%;
        }

        .lvl-26 {
            top: 81%;
            left: 73%;
        }

        .lvl-27 {
            top: 81%;
            left: 68%;
        }

        .lvl-28 {
            top: 81%;
            left: 63%;
        }

        .lvl-29 {
            top: 81%;
            left: 58%;
        }

        .lvl-30 {
            top: 81%;
            left: 53%;
        }

        .lvl-31 {
            top: 81%;
            left: 48%;
        }

        .lvl-32 {
            top: 81%;
            left: 43%;
        }

        .lvl-33 {
            top: 81%;
            left: 38%;
        }

        .lvl-34 {
            top: 81%;
            left: 33%;
        }

        .lvl-35 {
            top: 78%;
            left: 28%;
        }

        .lvl-36 {
            top: 70%;
            left: 25%;
        }

        .lvl-37 {
            top: 60%;
            left: 25%;
        }

        .lvl-38 {
            top: 53%;
            left: 30%;
        }

        .lvl-39 {
            top: 52%;
            left: 37%;
        }

        .lvl-40 {
            top: 52%;
            left: 43%;
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
        }

        input {
            width: 85%;
            padding: 14px;
            font-size: 20px;
            margin-top: 15px;
            border-radius: 8px;
            border: 1px solid #ccc;
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
            left: 120px;
            width: 80px;
            height: auto;
            z-index: 15;
            transition: left 0.5s linear, bottom 0.3s ease;
        }

        /* Victory message */
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

        /* Death message */
        .death-message {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: linear-gradient(135deg, #8B0000, #DC143C);
            color: white;
            padding: 30px 50px;
            border-radius: 20px;
            font-size: 32px;
            font-weight: bold;
            text-align: center;
            z-index: 200;
            display: none;
            box-shadow: 0 0 50px rgba(0, 0, 0, 0.5);
            animation: shake 0.5s ease;
        }

        @keyframes bounce {

            0%,
            100% {
                transform: translate(-50%, -50%) scale(1);
            }

            50% {
                transform: translate(-50%, -50%) scale(1.1);
            }
        }

        @keyframes shake {

            0%,
            100% {
                transform: translate(-50%, -50%) translateX(0);
            }

            25% {
                transform: translate(-50%, -50%) translateX(-10px);
            }

            75% {
                transform: translate(-50%, -50%) translateX(10px);
            }
        }

        /* Disabled state for game */
        .game-disabled {
            pointer-events: none;
            opacity: 0.7;
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
    </style>
</head>

<body>
    <img id="monkey" src="Idle__000.png">
    <div class="game-container" id="gameContainer">

        <video autoplay loop muted>
            <source src="assests/backgroundmap.mp4" type="video/mp4">
        </video>

        <div id="user-info">

            👤 Player: <strong><?php echo htmlspecialchars($current_user); ?></strong>

            <div id="score-display">
                🍌 Bananas:
                <span id="banana-count"><?php echo $current_bananas; ?></span>
            </div>

            <div id="level-progress" style="margin-top: 10px; font-size: 14px;">
                📍 Current Level: <span id="current-level-display">1</span>
            </div>

            <?php if ($is_logged_in): ?>
                <a href="logout.php" class="logout-btn">Logout</a>
            <?php endif; ?>

        </div>

        <a href="index.php" class="menu-btn">⬅ Return to Menu</a>

        <?php
        // Generate level buttons
        for ($i = 1; $i <= 40; $i++) {
            $unlocked_class = ($i <= $current_bananas + 1) ? 'unlocked' : '';
            $completed_class = ($i < $current_bananas + 1) ? 'completed' : '';
            echo "<button class='level-btn lvl-$i $unlocked_class $completed_class' onclick='handleLevelClick($i)'>$i</button>";
        }
        ?>

        <div id="puzzleBox" class="puzzle-overlay">

            <span class="close-btn" onclick="closePuzzle()">×</span>

            <h2 id="level-title"></h2>

            <div id="timer"></div>

            <div id="question-area"></div>

            <input type="number" id="user-answer" placeholder="Enter your answer">

            <button class="submit-btn" onclick="checkAnswer()">Submit</button>

        </div>

        <div id="victoryMessage" class="victory-message"></div>
        <div id="deathMessage" class="death-message"></div>
        <button id="resetBtn" class="reset-btn" onclick="resetGame()">🔄 Start Over</button>

    </div>

    <script>
        // ============================================
        // GAME STATE VARIABLES
        // ============================================
        const isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
        let bananaScore = <?php echo $current_bananas; ?>; // Current bananas count
        let correctAnswer = null; // Stores the correct answer for current puzzle
        let activeLevel = 1; // Currently active level being played
        let timer; // Timer interval reference
        let timeLeft; // Time remaining for current level
        let isDead = false; // Whether monkey is dead
        let isGameActive = true; // Whether game is active

        // Animation state variables
        let isMoving = false; // Whether monkey is currently walking
        let isJumping = false; // Whether monkey is currently jumping
        let currentTargetLevel = null; // Level that monkey is moving to
        let nextLevelToUnlock = bananaScore + 1; // Next level to unlock

        // ============================================
        // ANIMATION FRAMES
        // ============================================

        // Walking animation frames (0-7)
        let walkFrames = [
            "Walk__000.png",
            "Walk__001.png",
            "Walk__002.png",
            "Walk__003.png",
            "Walk__004.png",
            "Walk__005.png",
            "Walk__006.png",
            "Walk__007.png"
        ];

        // Jump animation frames (0-7)
        let jumpFrames = [
            "Jump__000.png",
            "Jump__001.png",
            "Jump__002.png",
            "Jump__003.png",
            "Jump__004.png",
            "Jump__005.png",
            "Jump__006.png",
            "Jump__007.png"
        ];

        // Idle animation frames (0-9)
        let idleFrames = [
            "Idle__000.png",
            "Idle__001.png",
            "Idle__002.png",
            "Idle__003.png",
            "Idle__004.png",
            "Idle__005.png",
            "Idle__006.png",
            "Idle__007.png",
            "Idle__008.png",
            "Idle__009.png"
        ];

        // Death animation frames (0-9)
        let deathFrames = [
            "Dead__000.png",
            "Dead__001.png",
            "Dead__002.png",
            "Dead__003.png",
            "Dead__004.png",
            "Dead__005.png",
            "Dead__006.png",
            "Dead__007.png",
            "Dead__008.png",
            "Dead__009.png"
        ];

        // Animation indices
        let idleIndex = 0;
        let walkIndex = 0;
        let jumpIndex = 0;
        let deathIndex = 0;

        // Animation interval references
        let idleInterval;
        let walkInterval;
        let jumpInterval;
        let deathInterval;

        // ============================================
        // ANIMATION CONTROL FUNCTIONS
        // ============================================

        /**
         * Start idle animation (monkey standing still)
         * Cycles through Idle__000.png to Idle__009.png
         */
        function startIdleAnimation() {
            if (isDead) return;
            stopAllAnimations();
            idleInterval = setInterval(() => {
                if (!isMoving && !isJumping && !isDead && isGameActive) {
                    const monkey = document.getElementById("monkey");
                    monkey.src = idleFrames[idleIndex];
                    idleIndex = (idleIndex + 1) % idleFrames.length;
                }
            }, 120);
        }

        /**
         * Start walking animation
         * Cycles through Walk__000.png to Walk__007.png
         */
        function startWalkAnimation() {
            if (isDead) return;
            stopAllAnimations();
            walkInterval = setInterval(() => {
                if (isMoving && !isJumping && !isDead && isGameActive) {
                    const monkey = document.getElementById("monkey");
                    monkey.src = walkFrames[walkIndex];
                    walkIndex = (walkIndex + 1) % walkFrames.length;
                }
            }, 100);
        }

        /**
         * Start jumping animation
         * Cycles through Jump__000.png to Jump__007.png
         */
        function startJumpAnimation() {
            if (isDead) return;
            stopAllAnimations();
            jumpInterval = setInterval(() => {
                if (isJumping && !isDead && isGameActive) {
                    const monkey = document.getElementById("monkey");
                    monkey.src = jumpFrames[jumpIndex];
                    jumpIndex = (jumpIndex + 1) % jumpFrames.length;

                    // Reset to idle after jump animation completes
                    if (jumpIndex === 0) {
                        setTimeout(() => {
                            isJumping = false;
                            startIdleAnimation();
                        }, 50);
                    }
                }
            }, 80);
        }

        /**
         * Start death animation when timer ends
         * Cycles through Dead__000.png to Dead__009.png
         */
        function startDeathAnimation() {
            isDead = true;
            isGameActive = false;
            stopAllAnimations();

            const monkey = document.getElementById("monkey");
            deathIndex = 0;

            deathInterval = setInterval(() => {
                if (deathIndex < deathFrames.length) {
                    monkey.src = deathFrames[deathIndex];
                    deathIndex++;
                } else {
                    // Death animation completed
                    clearInterval(deathInterval);
                    showDeathMessage();
                }
            }, 100);
        }

        /**
         * Show death message and reset option
         */
        function showDeathMessage() {
            const deathDiv = document.getElementById("deathMessage");
            deathDiv.innerHTML = "💀 GAME OVER! 💀<br>Time's up! You died!<br>🍌 Bananas lost: " + bananaScore;
            deathDiv.style.display = "block";

            // Show reset button
            document.getElementById("resetBtn").style.display = "block";

            // Disable level buttons
            const levelBtns = document.querySelectorAll('.level-btn');
            levelBtns.forEach(btn => {
                btn.style.pointerEvents = 'none';
            });

            setTimeout(() => {
                deathDiv.style.display = "none";
            }, 3000);
        }

        /**
         * Stop all running animations
         */
        function stopAllAnimations() {
            if (idleInterval) clearInterval(idleInterval);
            if (walkInterval) clearInterval(walkInterval);
            if (jumpInterval) clearInterval(jumpInterval);
            if (deathInterval) clearInterval(deathInterval);
        }

        /**
         * Reset the game
         */
        function resetGame() {
            // Reset game state
            bananaScore = 0;
            isDead = false;
            isGameActive = true;
            activeLevel = 1;

            // Update UI
            document.getElementById("banana-count").innerText = "0";
            document.getElementById("current-level-display").innerText = "1";

            // Save reset score to database
            saveScoreToDB(0);

            // Reset monkey position
            const monkey = document.getElementById("monkey");
            monkey.style.left = "120px";
            monkey.style.bottom = "60px";

            // Update level buttons
            updateLevelButtons();

            // Hide reset button
            document.getElementById("resetBtn").style.display = "none";

            // Enable level buttons
            const levelBtns = document.querySelectorAll('.level-btn');
            levelBtns.forEach(btn => {
                btn.style.pointerEvents = 'auto';
            });

            // Start idle animation
            startIdleAnimation();

            alert("Game has been reset! Start from Level 1! 🍌");
        }

        // ============================================
        // MONKEY MOVEMENT FUNCTIONS
        // ============================================

        /**
         * Get button position for monkey to move next to
         * @param {HTMLElement} button - The level button
         * @returns {Object} Position coordinates
         */
        function getPositionNextToButton(button) {
            const btnRect = button.getBoundingClientRect();
            // Position monkey to the left side of the button
            return {
                left: btnRect.left - 70, // 70px to the left of button
                top: btnRect.top + (btnRect.height / 2) - 40 // Center vertically with button
            };
        }

        /**
         * Move monkey to a specific level button position (next to it)
         * @param {number} level - Level number to move to
         * @param {boolean} openPuzzleAfterMove - Whether to open puzzle after movement
         */
        function moveMonkeyToLevel(level, openPuzzleAfterMove = true) {
            if (isDead) return;

            const levelBtn = document.querySelector(`.lvl-${level}`);
            if (!levelBtn) return;

            const targetPos = getPositionNextToButton(levelBtn);
            const monkey = document.getElementById("monkey");

            // Calculate target position
            const targetLeft = targetPos.left;
            const currentLeft = parseFloat(monkey.style.left) || 120;

            // Don't move if already at target position
            if (Math.abs(currentLeft - targetLeft) < 10) {
                performJump(openPuzzleAfterMove);
                return;
            }

            // Start walking animation
            isMoving = true;
            startWalkAnimation();

            // Move smoothly to target position
            const step = 5;
            const moveInterval = setInterval(() => {
                const currentPos = parseFloat(monkey.style.left) || 120;
                const diff = targetLeft - currentPos;

                if (Math.abs(diff) < step) {
                    // Reached target
                    monkey.style.left = targetLeft + "px";
                    clearInterval(moveInterval);
                    isMoving = false;
                    performJump(openPuzzleAfterMove);
                } else {
                    // Move step by step
                    const newPos = currentPos + (diff > 0 ? step : -step);
                    monkey.style.left = newPos + "px";
                }
            }, 16);
        }

        /**
         * Perform jump animation at current position
         * @param {boolean} openPuzzleAfterJump - Whether to open puzzle after jump
         */
        function performJump(openPuzzleAfterJump = true) {
            if (isDead) return;

            isJumping = true;
            const monkey = document.getElementById("monkey");
            const originalBottom = 60;

            startJumpAnimation();

            // Jump physics - move up and down smoothly
            let jumpHeight = 0;
            let goingUp = true;

            const jumpInterval = setInterval(() => {
                if (!isJumping || isDead) {
                    clearInterval(jumpInterval);
                    monkey.style.bottom = originalBottom + "px";
                    if (!isDead) startIdleAnimation();
                    return;
                }

                if (goingUp) {
                    jumpHeight += 15;
                    monkey.style.bottom = (originalBottom + jumpHeight) + "px";
                    if (jumpHeight >= 80) {
                        goingUp = false;
                    }
                } else {
                    jumpHeight -= 15;
                    monkey.style.bottom = (originalBottom + jumpHeight) + "px";
                    if (jumpHeight <= 0) {
                        clearInterval(jumpInterval);
                        isJumping = false;
                        monkey.style.bottom = originalBottom + "px";
                        if (!isDead) startIdleAnimation();
                        // Open puzzle after jump is complete
                        if (openPuzzleAfterJump && currentTargetLevel && !isDead) {
                            openPuzzleAfterMove(currentTargetLevel);
                            currentTargetLevel = null;
                        }
                    }
                }
            }, 30);
        }

        // ============================================
        // LEVEL MANAGEMENT FUNCTIONS
        // ============================================

        /**
         * Update UI to show unlocked and completed levels
         */
        function updateLevelButtons() {
            // Update button classes based on banana score
            for (let i = 1; i <= 40; i++) {
                const btn = document.querySelector(`.lvl-${i}`);
                if (btn) {
                    // Remove existing classes
                    btn.classList.remove('unlocked', 'completed', 'current');

                    // Add appropriate class
                    if (i < bananaScore + 1) {
                        btn.classList.add('completed');
                    } else if (i === bananaScore + 1) {
                        btn.classList.add('unlocked', 'current');
                    } else if (i <= bananaScore + 1) {
                        btn.classList.add('unlocked');
                    }
                }
            }

            // Update current level display
            document.getElementById('current-level-display').innerText = bananaScore + 1;
        }

        /**
         * Handle level button click
         * @param {number} level - Level number clicked
         */
        function handleLevelClick(level) {
            // Check if game is active
            if (isDead) {
                alert("You are dead! Click 'Start Over' to play again!");
                return;
            }

            // Check if user is logged in
            if (!isLoggedIn) {
                alert("Please login to play");
                window.location.href = "login.php";
                return;
            }

            // Check if level is unlocked
            if (level > bananaScore + 1) {
                alert("Complete Level " + (bananaScore + 1) + " first to unlock Level " + level + "! 🍌");
                return;
            }

            // Check if level is already completed
            if (level < bananaScore + 1) {
                alert("You've already completed Level " + level + "! Move to the next level! 🎉");
                return;
            }

            // Set target level and start movement
            currentTargetLevel = level;
            moveMonkeyToLevel(level);
        }

        /**
         * Open puzzle after monkey finishes moving
         * @param {number} level - Level to open puzzle for
         */
        function openPuzzleAfterMove(level) {
            if (isDead) return;
            activeLevel = level;
            openPuzzle();
        }

        /**
         * Start timer for current level
         * Timer duration depends on level difficulty
         * @param {number} level - Current level number
         */
        function startTimer(level) {
            // Set time based on level difficulty
            if (level < 10) {
                timeLeft = 60; // 60 seconds for levels 1-9
            } else if (level < 20) {
                timeLeft = 30; // 30 seconds for levels 10-19
            } else if (level < 35) {
                timeLeft = 20; // 20 seconds for levels 20-34
            } else {
                timeLeft = 10; // 10 seconds for levels 35-40
            }

            clearInterval(timer);

            timer = setInterval(function() {
                if (!isDead && isGameActive) {
                    document.getElementById("timer").innerText = "⏱ " + timeLeft + " sec";
                    timeLeft--;

                    if (timeLeft < 0) {
                        clearInterval(timer);
                        // Time's up! Monkey dies
                        closePuzzle();
                        startDeathAnimation();
                    }
                }
            }, 1000);
        }

        /**
         * Open puzzle popup and load question
         */
        async function openPuzzle() {
            if (isDead) return;

            const box = document.getElementById("puzzleBox");
            const area = document.getElementById("question-area");
            document.getElementById("level-title").innerText = "Level " + activeLevel;
            document.getElementById("user-answer").value = "";
            box.style.display = "block";
            area.innerHTML = "Loading puzzle...";
            startTimer(activeLevel);

            try {
                // Fetch puzzle from API
                const response = await fetch("https://marcconrad.com/uob/banana/api.php");
                const data = await response.json();
                correctAnswer = data.solution;
                area.innerHTML = `<img src="${data.question}" alt="Puzzle Question">`;
            } catch {
                area.innerHTML = "Puzzle loading failed. Please try again!";
            }
        }

        /**
         * Check user's answer against correct answer
         * If correct, award banana and move to next level
         */
        function checkAnswer() {
            if (isDead) {
                alert("You are dead! Please reset the game to continue!");
                closePuzzle();
                return;
            }

            let input = document.getElementById("user-answer").value;

            if (input == correctAnswer) {
                // Correct answer!
                clearInterval(timer);
                bananaScore++;
                document.getElementById("banana-count").innerText = bananaScore;

                // Check if game is completed
                if (bananaScore === 40) {
                    showVictoryMessage();
                }

                // Save score to database
                saveScoreToDB(bananaScore);

                // Update level buttons UI
                updateLevelButtons();

                alert("Correct! 🍌 +1 Banana!");
                closePuzzle();

                // Automatically move to next level
                const nextLevel = activeLevel + 1;
                if (nextLevel <= 40) {
                    currentTargetLevel = nextLevel;
                    moveMonkeyToLevel(nextLevel);
                } else {
                    // Game completed
                    alert("Congratulations! You've completed all 40 levels! 🎉🎉🎉");
                }
            } else {
                // Wrong answer
                alert("Wrong answer! Try again! 🤔");
                document.getElementById("user-answer").value = "";
            }
        }

        /**
         * Show victory message when game is completed
         */
        function showVictoryMessage() {
            const victoryDiv = document.getElementById("victoryMessage");
            victoryDiv.innerHTML = "🎉🏆 VICTORY! 🏆🎉<br>You've completed all 40 levels!<br>🍌🍌🍌🍌🍌";
            victoryDiv.style.display = "block";
            setTimeout(() => {
                victoryDiv.style.display = "none";
            }, 5000);
        }

        /**
         * Close puzzle popup
         */
        function closePuzzle() {
            document.getElementById("puzzleBox").style.display = "none";
            clearInterval(timer);
        }

        /**
         * Save current score to database
         * @param {number} score - Current banana score
         */
        function saveScoreToDB(score) {
            fetch("save_score.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "score=" + score
            });
        }

        // ============================================
        // INITIALIZATION
        // ============================================

        // Initialize idle animation
        startIdleAnimation();

        // Set initial monkey position
        const monkey = document.getElementById("monkey");
        monkey.style.left = "120px";
        monkey.style.bottom = "60px";

        // Update level buttons based on current score
        updateLevelButtons();

        // If user has completed some levels, move monkey to current level position
        if (bananaScore > 0) {
            setTimeout(() => {
                const currentLevel = bananaScore + 1;
                moveMonkeyToLevel(currentLevel, false);
            }, 1000);
        }
    </script>

</body>

</html>