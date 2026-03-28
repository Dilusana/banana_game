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
<header class="top-header">

    <div class="left-buttons">
        <?php if (!$is_logged_in): ?>
            <button class="header-btn" onclick="window.location.href='login.php'">Login</button>
        <?php else: ?>
            <button class="header-btn" onclick="window.location.href='logout.php'">Logout</button>
        <?php endif; ?>
        
        <button class="header-btn" onclick="window.location.href='leaderboard.php'">Leaderboard</button>
    </div>

    <div class="right-info">
        <div class="info-box">
            <img src="assests/monkey.png" class="icon" alt="monkey">
            <span class="username"><?php echo htmlspecialchars($current_user); ?></span>
        </div>

        <div class="info-box">
            <img src="assests/banana.png" class="icon" alt="banana">
            <span class="banana-count"><?php echo $current_bananas; ?></span>
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