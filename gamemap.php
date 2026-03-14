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

    $conn = new mysqli("localhost", "root", "", "banana_game", 3307);

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
        }

        .level-btn:hover {
            transform: scale(1.2);
            background: gold;
            color: black;
        }

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
    </style>
</head>

<body>

    <div class="game-container">

        <video autoplay loop muted>
            <source src="assests/backgroundmap.mp4" type="video/mp4">
        </video>

        <div id="user-info">

            👤 Player: <strong><?php echo htmlspecialchars($current_user); ?></strong>

            <div id="score-display">
                🍌 Bananas:
                <span id="banana-count"><?php echo $current_bananas; ?></span>
            </div>

            <?php if ($is_logged_in): ?>
                <a href="logout.php" class="logout-btn">Logout</a>
            <?php endif; ?>

        </div>

        <a href="index.php" class="menu-btn">⬅ Return to Menu</a>

        <?php
        for ($i = 1; $i <= 40; $i++) {
            echo "<button class='level-btn lvl-$i' onclick='handleLevelClick($i)'>$i</button>";
        }
        ?>

        <div id="puzzleBox" class="puzzle-overlay">

            <span class="close-btn" onclick="closePuzzle()">×</span>

            <h2 id="level-title"></h2>

            <div id="timer"></div>

            <div id="question-area"></div>

            <input type="number" id="user-answer" placeholder="?">

            <button class="submit-btn" onclick="checkAnswer()">Submit</button>

        </div>

    </div>

    <script>
        const isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;

        let bananaScore = <?php echo $current_bananas; ?>;

        let correctAnswer = null;

        let activeLevel = 1;

        let timer;
        let timeLeft;

        function handleLevelClick(level) {

            if (!isLoggedIn) {
                alert("Please login to play");
                window.location.href = "login.php";
                return;
            }

            if (bananaScore < (level - 1)) {
                alert("Earn " + (level - 1) + " bananas to unlock Level " + level + " 🍌");
                return;
            }

            activeLevel = level;

            openPuzzle();
        }

        function startTimer(level) {

            if (level < 10) {
                timeLeft = 60;
            } else if (level < 20) {
                timeLeft = 30;
            } else if (level < 35) {
                timeLeft = 20;
            } else {
                timeLeft = 10;
            }

            clearInterval(timer);

            timer = setInterval(function() {

                document.getElementById("timer").innerText = "⏱ " + timeLeft + " sec";

                timeLeft--;

                if (timeLeft < 0) {

                    clearInterval(timer);

                    alert("Time Up!");

                    closePuzzle();

                }

            }, 1000);

        }

        async function openPuzzle() {

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

                area.innerHTML = `<img src="${data.question}">`;

            } catch {

                area.innerHTML = "Puzzle loading failed";

            }

        }

        function checkAnswer() {

            let input = document.getElementById("user-answer").value;

            if (input == correctAnswer) {

                clearInterval(timer);

                bananaScore++;

                document.getElementById("banana-count").innerText = bananaScore;

                alert("Correct! 🍌");

                saveScoreToDB(bananaScore);

                closePuzzle();

            } else {

                alert("Wrong answer");

            }

        }

        function closePuzzle() {

            document.getElementById("puzzleBox").style.display = "none";

            clearInterval(timer);

        }

        function saveScoreToDB(score) {

            fetch("save_score.php", {

                method: "POST",

                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },

                body: "score=" + score

            });

        }
    </script>

</body>

</html>